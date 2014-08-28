var io = require('socket.io').listen(8008);
var mysql = require('mysql');

var creds = {
	host: 'localhost',
	user: 'root',
	password: 'root',
	database: 'thscs',
	port: 3306,
	_socket: '/var/run/mysqld/mysqld.sock'
};

var start_time = null;
var pause_time = null;
var refreshed_at_end = false;

var clients = {};
var teams = {};
var admin = null;

var m = mysql.createConnection(creds);

var toObject = function(arr) {
  var rv = {};
  for (var i = 0; i < arr.length; ++i)
    if (arr[i] !== undefined) rv[i] = arr[i];
  return rv;
}

var time_interval = function() {
	var now_time = new Date();
	var rem = remaining_time(now_time);
	if(rem > 0 && rem < 1000*60*60*2&&pause_time===null)
		return({time:rem, status:'running'});
	else if (pause_time!==null) return({time:rem, status:'paused'});
	else if (rem < 0){
		return({time: 0, status:'stopped'});
	}
	else{
		return({time: rem, status:'stopped'});		
	}
}
var intvl = setInterval(function(){
	var now_time = new Date();
	var rem = remaining_time(now_time);
	if(rem > 0 && rem < 1000*60*60*2&&pause_time===null)
		io.sockets.emit('time',{time:rem, status:'running'});
	else if (pause_time!==null) io.sockets.emit('time',{time:rem, status:'paused'});
	else if (rem < 0){
		if(!refreshed_at_end) io.sockets.emit('refresh');
		refreshed_at_end = true;
		io.sockets.emit('time',{time: 0, status:'stopped'});
		
		//clearInterval(intvl);
	}
	else{
		io.sockets.emit('time',{time: rem, status:'stopped'});		
	}
	
},1000);

var remaining_time = function(now_time){
	if(!start_time) return 1000*60*60*2; // 2 hours
	else if(pause_time){
		return 1000*60*60*2 - (now_time.getTime() - start_time.getTime() - (now_time.getTime() - pause_time.getTime())  );	
	}
	return 1000*60*60*2 - (now_time.getTime() - start_time.getTime());
}

io.sockets.emit('refresh');

io.sockets.on('connection', function(socket) {
	var __authenticateUser = function(team, auth,cb) {
		m.query("SELECT (auth) FROM teams WHERE (`team`="+m.escape(team)+")", function(err, result, fields) {
			if(err) {
		        console.error(err);
		    } else if (result.length  > 0) {
		    	if(result[0].auth===auth) {
		    		teams[team] = socket.id;
					clients[socket.id] = socket;
		    	} else {
		    		console.error('Unable to authenticate user: '+ team);
		    	}
		    }
		    cb();
		});
	}
	socket.on('team', function(data) {
		__authenticateUser(data.team, data.auth, function () {
			console.log(teams);
		});
	});
});














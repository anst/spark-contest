var io = require('socket.io').listen(8008);
var mysql = require('mysql');

function toObject(arr) {
  var rv = {};
  for (var i = 0; i < arr.length; ++i)
    if (arr[i] !== undefined) rv[i] = arr[i];
  return rv;
}

io.sockets.on('connection', function (socket) {
	socket.on('get_clars', function(data) {
		team = data.team;
		if(team!=undefined) {
			var m = mysql.createConnection({
			  host     : 'localhost',
			  user     : 'root',
			  password : 'AwesomeSauce',
			  database : 'thscs',
			  port     : 8889,
			  _socket: '/var/run/mysqld/mysqld.sock',
			});
			m.query("SELECT * FROM clarifications WHERE (`from`="+m.escape(team)+" OR `global`='yes') ORDER BY `id` DESC", function(err, result, fields) {
				if(err) {
		            m.end();
		            console.error(err);
		            return;
		        } else if (result.length  > 0) {
			        return socket.emit('clarifications', toObject(result));
			    }
			});
		} else socket.emit('clarifications', {});
	});
	//socket.emit('refresh');
	
	socket.on('clarification', function (data) {
		var m = mysql.createConnection({
		  host     : 'localhost',
		  user     : 'root',
		  password : 'AwesomeSauce',
		  database : 'thscs',
		  port     : 8889,
		  _socket: '/var/run/mysqld/mysqld.sock',
		});
	    var post  = {from: data.from, problem: data.problem, message: data.message,reply:'', global: 'No'};
		m.query('INSERT INTO clarifications SET ?', post, function(err, result) {
			if(err) {
	            m.end();
	            console.error(err);
	            return;
	        }
			console.log("New clarifiction from team" + data.from+ " about problem #"+data.problem+"\nMessage:\n"+data.message);
			socket.emit('soft_refresh');
			console.log('REFRESHINSDOGINSDOIGNSODIGN');
		});
  	});
});
/*
** CONTEST PORTAL v3 
** Created By Andy Sturzu (sturzu.org) and Jonathan Zong (jonathanzong.com)
*/
var io = require('socket.io').listen(8008);
var mysql = require('mysql');
var creds = {host: 'localhost',user: 'root',password: 'AwesomeSauce',database: 'thscs',port: 3306,_socket: '/var/run/mysqld/mysqld.sock',};
io.set('log level', 1);
var clients = {};
var teams = {};
var admin = null;
function toObject(arr) {
  var rv = {};
  for (var i = 0; i < arr.length; ++i)
    if (arr[i] !== undefined) rv[i] = arr[i];
  return rv;
}
io.sockets.on('connection', function (socket) {
	socket.on('team', function(data) {
		teams[data.team] = socket.id;
		clients[socket.id] = socket;
	});
	socket.on('admin', function(data) {
		if(!auth(data.key)){
			return;
		}
		admin = socket;
	});
	socket.on('get_clars', function(data) {
		team = data.team;
		if(team!=undefined) {
			var m = mysql.createConnection(creds);
			m.query("SELECT * FROM clarifications WHERE (`from`="+m.escape(team)+" OR `global`='yes') ORDER BY `global`, `reply`='',`id` DESC", function(err, result, fields) {
				if(err) {
		            m.end();
		            console.error(err);
		            return;
		        } else if (result.length  > 0) {
		        	m.end();
		        	if(admin!=null) {
		        		admin.emit('soft_refresh',{key:'a2d99befaf381755257420f5f46e8838'});
		        	}
		        		
			        return socket.emit('clarifications', toObject(result));
			    }
			});
		} else {m.end(); socket.emit('clarifications', {})};
	});
	socket.on('get_admin_clars', function(data) {
		if(!auth(data.key)){
			socket.emit('admin_clarifications', {error:'auth error'});
			return;
		}
		
		var m = mysql.createConnection(creds);
		m.query("SELECT * FROM clarifications WHERE `reply`='' ORDER BY `id`", function(err, result, fields) {
			if(err) {
	            m.end();
	            console.error(err);
	            return;
	        } else if (result.length  > 0) {
	        	m.end();
		        return admin.emit('admin_clarifications', toObject(result));
		    } else return admin.emit('admin_clarifications',{});
		});
	});
	socket.on('get_subs', function(team) {
		//don't forget to overwrite output if contest is running
		if(team!=undefined) {
			var m = mysql.createConnection(creds);
			m.query("SELECT * FROM submissions WHERE `team`="+m.escape(team)+" ORDER BY `time` DESC", function(err, result, fields) {
				if(err) {
		            m.end();
		            console.error(err);
		            return;
		        } else if (result.length  > 0) {
		        	m.end();
		        	if(time_internal().status=="running"||time_internal().status=="paused"||
		        		time_internal().time==7200000) {
		        		for (var o in result) {
						   result[o]['output']="Available after the contest!";
						   result[o]['real_output']="Available after the contest!";
						}
		        	}
			        return clients[teams[team]].emit('submissions', toObject(result));
			    }
			});
		} else {m.end(); return socket.emit('submissions',{})};
	});
	socket.on('admin_clarification', function (data) {
		if(!auth(data.key)){
			return;
		}		
		var m = mysql.createConnection(creds);
		m.query('UPDATE `clarifications` SET `reply`='+m.escape(data.reply)+', `global`='+m.escape(data.global)+' WHERE `id`='+m.escape(data.id), function(err, result) {
			if(err) {
	            m.end();
	            console.error(err);
	            return;
	        }
			socket.broadcast.emit('soft_refresh');
			m.end();
		});
  	});
	socket.on('clarification', function (data) {
		var m = mysql.createConnection(creds);
	    var post  = {from: data.from, problem: data.problem, message: data.message,reply:'', global: 'No'};
		m.query('INSERT INTO clarifications SET ?', post, function(err, result) {
			if(err) {
	            m.end();
	            console.error(err);
	            return;
	        }
			console.log("New clarifiction from team" + data.from+ " about problem #"+data.problem+"\nMessage:\n"+data.message);
			socket.emit('soft_refresh');
			m.end();
		});
  	});
	socket.on('appeal', function (data) {
		console.log(data.id);
		var m = mysql.createConnection(creds);
		m.query('UPDATE `submissions` SET `appealed`=\'Yes\' WHERE subid='+m.escape(data.id)+';', function(err, result) {
			if(err) {
	            m.end();
	            console.error(err);
	            return;
	        } socket.emit('soft_refresh');
			m.end();
		});
		
  	});
  	// dumps table
  	socket.on('scoreboard', function (data) {
		division = data.division;
		if(division && (division === 'Novice' || division === 'Advanced')) {
			var m = mysql.createConnection({
			  host     : 'localhost',
			  user     : 'root',
			  password : 'AwesomeSauce',
			  database : 'thscs',
			  port     : 8889,
			  _socket: '/var/run/mysqld/mysqld.sock',
			});
			m.query("SELECT * FROM clarifications WHERE `division`='"+m.escape(division)+"' ORDER BY `score` DESC", function(err, result, fields) {
				if(err) {
		            m.end();
		            console.error(err);
		            return;
		        } else if (result.length  > 0) {
		        	m.end();
			        return socket.emit('scoreboard', toObject(result));
			    }
			});
		} else {m.end(); socket.emit('scoreboard', {})};
  	});

  	socket.on('recalculate', function (data) {
		team = data.team;
		console.log("hey recalc");
		if(team) {
			var m = mysql.createConnection(creds);
			m.query("SELECT * FROM submissions WHERE `team`="+m.escape(team)+" ORDER BY `time`", function(err, result, fields) {
				if(err) {
		            m.end();
		            console.error(err);
		            return;
		        } else if (result.length  > 0) {
		        	var sum = 0;
		        	var probs = {};
		        	for (var i = 0; i < result.length; i++){
		        		if(result[i]){
		        			if(!probs[result[i].problem])
		        				probs[result[i].problem] = 0;
		        			if(probs[result[i].problem] <= 0){		        				
			        			if(result[i].success === 'Yes'){
			        				probs[result[i].problem] += 60;
			        			}
			        			else{
			        				probs[result[i].problem] -= 5;
			        			}
		        			}
		        		}
		        	}
		        	for(var x in probs){
		        		if(probs.hasOwnProperty(x)){
		        			if(probs[x] > 0)
		        				sum += probs[x];
		        		}
		        	}
		        	m.query('UPDATE scoreboard SET score='+sum+' WHERE team='+team+';', function(err, result) {
						if(err) {
				            m.end();
				            console.error(err);
				            return;
				        }
						console.log("New clarifiction from team" + data.from+ " about problem #"+data.problem+"\nMessage:\n"+data.message);
						socket.emit('soft_refresh');
					});
					m.end();
			    	return socket.emit('recalculate', {score: sum});
			    }
			});
		} else {m.end(); socket.emit('recalculate', {error:'needs team number'})};
  	});
	var auth = function(key){
		return key === 'a2d99befaf381755257420f5f46e8838';
	}
	socket.on('start_time', function (data) {
		if(!auth(data.key)){
			socket.emit('time_error', {error:'auth error'});
			return;
		}
		var now_time = new Date();
		if( !start_time ){
            start_time = now_time;
        } else{
			socket.emit('time_error', {error:'contest already started', status:'running'});
			return;
        }
  	});
  	socket.on('pause_time', function (data) {
		if(!auth(data.key)){
			socket.emit('time_error', {error:'auth error'});
			return;
		}
		var now_time = new Date();
		if( !start_time ){
			socket.emit('time_error', {error:'contest not started', status:'stopped'});
			return;
        } else if( !pause_time ){
            pause_time = now_time;
        } else {
        	socket.emit('time_error', {error:'contest already paused', status:'paused'});
			return;
        }
  	});
  	socket.on('resume_time', function (data) {
		if(!auth(data.key)){
			socket.emit('time_error', {error:'auth error'});
			return;
		}
		var now_time = new Date();
		if(!start_time){
			socket.emit('time_error', {error:'contest not started', status:'stopped'});
        } else if(pause_time){
            start_time = new Date(start_time.getTime() + (now_time - pause_time));
            pause_time = null;
        } else{
        	socket.emit('time_error', {error:'contest already running', status:'running'});
			return;        	
        }
  	});
  	socket.on('increment_time', function (data) {
		if(!auth(data.key)){
			socket.emit('increment_time', {error:'auth error'});
			return;
		}
		if(!start_time){
			socket.emit('time_error', {error:'contest not started', status:'stopped'});
        } else if(data.inc){ // make sure inc is in minutes
            start_time = new Date(start_time.getTime() + data.inc*60*1000);
        }
  	});
});
var start_time = null;
var pause_time = null;
var refreshed_at_end = false;
function time_internal() {
	var now_time = new Date();
	var rem = remaining_time(now_time);
	if(rem > 0 && rem < 1000*60*60*1/240&&pause_time===null)
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
	if(rem > 0 && rem < 1000*60*60*1/240&&pause_time===null)
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
// millis
var remaining_time = function(now_time){
	if(!start_time) return 1000*60*60*1/240; // 2 hours
	else if(pause_time){
		return 1000*60*60*1/240 - (now_time.getTime() - start_time.getTime() - (now_time.getTime() - pause_time.getTime())  );	
	}
	return 1000*60*60*1/240 - (now_time.getTime() - start_time.getTime());
}
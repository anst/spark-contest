var io = require('socket.io').listen(8008);
var mysql = require('mysql');

io.set('log level', 1);

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
			m.query("SELECT * FROM clarifications WHERE (`from`="+m.escape(team)+" OR `global`='yes') ORDER BY `global`, `reply`='',`id` DESC", function(err, result, fields) {
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
			        return socket.emit('scoreboard', toObject(result));
			    }
			});
		} else socket.emit('scoreboard', {});
  	});

  	// dumps table
  	socket.on('recalculate', function (data) {
		team = data.team;
		if(team) {
			var m = mysql.createConnection({
			  host     : 'localhost',
			  user     : 'root',
			  password : 'AwesomeSauce',
			  database : 'thscs',
			  port     : 8889,
			  _socket: '/var/run/mysqld/mysqld.sock'
			});
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
			    	return socket.emit('recalculate', {score: sum});
			    }
			});
		} else socket.emit('recalculate', {error:'needs team number'});
  	});
});
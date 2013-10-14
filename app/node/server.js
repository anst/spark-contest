var io = require('socket.io').listen(8008);
var mysql = require('mysql');

var m = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : 'AwesomeSauce',
  database : 'thscs',
  port     : 8889,
  _socket: '/var/run/mysqld/mysqld.sock',
});
io.sockets.on('connection', function (socket) {
	socket.emit('refresh');
	socket.on('clarification', function (data) {
	    var post  = {from: 1, problem: 1, message: data.message, global: 'No'};
		m.query('INSERT INTO clarifications SET ?', post, function(err, result) {
			if(err) {

	            m.end();
	            console.error(err);
	            return;
	        }
			console.log("inserted clarification");
		});
  	}, function() { m.end();});
});
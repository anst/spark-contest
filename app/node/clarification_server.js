var io = require('socket.io').listen(8008);
var mysql = require('mysql');


io.sockets.on('connection', function (socket) {
	var m = mysql.createConnection({
	  host     : 'localhost',
	  user     : 'root',
	  password : 'AwesomeSauce',
	  database : 'thscs',
	  port     : 8889,
	});
	m.connect();
	socket.on('clarification', function (data) {
	    var post  = {from: 1, problem: 1, message: data.message, global: 'No'};
		m.query('INSERT INTO clarifications SET ?', post, function(err, result) {
			if(err) {
	            m.end();
	            console.error(err);
	            return;
	        }
			m.end();
			console.log("inserted clarification");
		});
  	});
});
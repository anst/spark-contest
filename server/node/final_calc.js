var mysql = require('mysql');
var creds = {host: 'localhost',user: 'root',password: 'AwesomeSauce',database: 'thscs',port: 3306,_socket: '/var/run/mysqld/mysqld.sock',};
for (var team=1;team<=64;team++){
	var m = mysql.createConnection(creds);
	m.query("SELECT * FROM submissions WHERE `team`="+m.escape(team)+" ORDER BY `time`", function(err, result, fields) {
		if(err) {
            console.error(err);
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

            console.log(team);
        	m.query('UPDATE scoreboard SET score='+sum+' WHERE team='+result[0].team+';', function(err, result) {
				if(err) {
		            console.error(err);
		            return;
		        }
				
			});
	    	
	    }

	});
}
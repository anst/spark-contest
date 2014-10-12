clean() {
    echo "Received SIGINT, safely closing processes."
    killall php &> /dev/null
    killall node &> /dev/null
    clear
}
load() {
    for i in `seq 1 5`;
    do
        echo '|'
        sleep .1
        clear
        echo '/'
        sleep .1
        clear
        echo '-'
        sleep .1
        clear
        echo '\'
        sleep .1
        clear
    done
}

load
trap clean SIGINT
clear
echo $(tput setaf 3)

echo '+--------------------------------------------------+'
echo '+  ____  ____   _    ____  _  __   ____ _     ___  +'
echo '+ / ___||  _ \\ / \\  |  _ \\| |/ /  / ___| |   |_ _| +'
echo '+ \\___ \\| |_) / _ \\ | |_) | " /  | |   | |    | |  +'
echo '+  ___) |  __/ ___ \\|  _ <| . \\  | |___| |___ | |  +'
echo '+ |____/|_| /_/   \\_\\_| \\_\\_|\\_\\  \\____|_____|___| +'
echo '+              written by Andy Sturzu              +'
echo '+--------------------------------------------------+'
echo '+   Running all of the required files. Have Fun!   +'
echo '+--------------------------------------------------+'

echo

killall php &> /dev/null
killall node &> /dev/null
node server/server.js > logs/log.txt &
php server/judge.php > logs/log.txt &
tail -f logs/log.txt;

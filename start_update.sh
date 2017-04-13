#!/bin/sh

cd /var/www/

screen -qdmS SaveDepth sh -c ./save_depth.sh
screen -qdmS SaveTrades sh -c ./save_trades.sh
screen -qdmS SaveTicker sh -c ./save_ticker.sh
screen -qdmS WSReceiver sh -c ./ws_receiver.sh
screen -qdmS JSDataFeed sh -c ./js_datafeed.sh

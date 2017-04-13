#!/bin/sh

cd /var/www
screen -qdmS SaveTrades sh -c save_trades.sh
screen -qdmS SaveTicker sh -c save_ticker.sh
screen -qdmS SaveDepth sh -c save_depth.sh


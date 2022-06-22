#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

curl --insecure \
  -X GET "$ApiUrl" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/html"

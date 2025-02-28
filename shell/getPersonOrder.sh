#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

PersonId=1187671

# note that I cannt retrieve these old orders
#OrderId=13488792
#OrderId=13488781

#OrderId=2438817
#OrderId=2438822
OrderId=2438674

# &startRow=${startRow}&pageRows=${pageRows}
startRow=1
pageRows=10

curl --insecure --silent \
  -X GET "${ApiUrl}Persons/${PersonId}/Orders/${OrderId}/?CountryCode=${CountryCode}" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml"

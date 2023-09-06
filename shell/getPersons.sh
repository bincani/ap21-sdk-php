#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

#Query=&surname={surname}&firstname={firstname}&phone={phone}&email={email}&code={code}&password={password}
Query="&surname=Incani"

#curl --insecure --silent \
#  -X GET "$ApiUrl/Persons?CountryCode=$CountryCode$Query" \
#  -u "$ApiUser:$ApiPassword" \
#  -H "Content-Type: application/xml"

#curl --insecure --silent \
#  -X GET "$ApiUrl/Persons?CountryCode=$CountryCode" \
#  -u "$ApiUser:$ApiPassword" \
#  -H "Content-Type: application/xml"

# /Persons/{personId}/Orders/?countryCode={countryCode}&startRow={startRow}&pageRows={pageRows}&updatedAfter={timestamp}
#curl --insecure \
##  -X GET "$ApiUrl/Persons?CountryCode=$CountryCode&CustomData=false&startRow=1&pageRows=10" \
# -u "$ApiUser:$ApiPassword" \
#  -H "Content-Type: application/xml" > "people.xml" &

Query="&email=kateandnick2009@gmail.com"

curl --insecure \
  -X GET "$ApiUrl/Persons?CountryCode=$CountryCode&CustomData=false$Query" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml" > "people.xml"

# &startRow={startRow}&pageRows={pageRows}&updatedAfter={timestamp}
PersonId=803607

curl --insecure \
  -X GET "$ApiUrl/Persons/$PersonId/Orders/?countryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml" > "orders.xml"

OrderId=1667576

curl --insecure \
  -X GET "$ApiUrl/Persons/$PersonId/Shipments/$OrderId?countryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Content-Type: application/xml" > "shipments.xml"

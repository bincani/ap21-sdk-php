#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

#VoucherNumber=20200015
#Pin=1966

#VoucherNumber=50084937
#Pin=5135

VoucherNumber=20200022
Pin=4005

curl --insecure -v \
  -X GET "${ApiUrl}Voucher/${VoucherNumber}?pin=${Pin}&CountryCode=$CountryCode" \
  -u "$ApiUser:$ApiPassword" \
  -H "Accept: version_2.0" \
  -H "Content-Type: application/xml"

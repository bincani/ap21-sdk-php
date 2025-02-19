#!/bin/bash

set -x

# load env into environment vars
if [ -f ./../.env ]; then
    export $(cat ./../.env | grep -v '#' | sed 's/\r$//' | awk '/=/ {print $1}' )
fi

StoreId=5509
StoreId=5450

#stores=(5401 5403 5404 5405 5406 5409 5410 5411 5412 5413 5414 5415 5422 5423 5424 5425 5426 5427 5428 5429 5430 5431 5433 5434 5435 5436 5437 5439 5440 5508 6461 6462 6463 6482 6503 6524 6605 7009 8450 8451 9430 11790 11929 12071 12710 14151 14511 15570 16190)
ChangedSince=2024-07-01T00:00:00
stores=(5501 5503 5418)

#-X GET "$ApiUrl/StockChanged?ChangedSince=$ChangedSince&StoreId=$StoreId&CountryCode=$CountryCode"

for StoreId in "${stores[@]}"
do
  curl --insecure \
    -X GET "$ApiUrl/StockChanged?ChangedSince=$ChangedSince&StoreId=$StoreId&CountryCode=$CountryCode" \
    -u "$ApiUser:$ApiPassword" \
    -H "Accept: version_4.0" \
    -H "Content-Type: application/xml" \
    > store-stock-change-$StoreId.xml &
done


# Rewards

**SDK Status: NOT IMPLEMENTED**

All rewards endpoints use `Accept: version_1.0`.

## Endpoints Summary
| Method | URI | Description |
|--------|-----|-------------|
| GET | /Rewards/Programs{/programId}?countryCode={cc} | List reward programs |
| GET | /Rewards/Accounts/{accountId}?countryCode={cc} | Account details |
| GET | /Rewards/Accounts/{accountId}/Transactions?countryCode={cc} | Transaction history |
| POST | /Rewards/Accounts?countryCode={cc} | Create account for person |
| PUT | /Rewards/Accounts/{accountId}?countryCode={cc} | Update tier |
| POST | /Rewards/Accounts/{accountId}/Rewards/?countryCode={cc} | Add rewards |
| POST | /Rewards/Accounts/{accountId}/Gifts/?countryCode={cc} | Add gifts |
| POST | /Rewards/Accounts/{accountId}/Points/?countryCode={cc} | Add points |
| POST | /Rewards/Accounts/{accountId}/Rewards/Redemptions?countryCode={cc} | Redeem rewards |
| POST | /Rewards/Accounts/{accountId}/Gifts/Redemptions?countryCode={cc} | Redeem gifts |
| POST | /Rewards/Accounts/{accountId}/Points/Redemptions?countryCode={cc} | Redeem points |
| POST | /Rewards/Confirmation?countryCode={cc}&requestId={guid} | Confirm redemption |
| POST | /Rewards/Reversal?countryCode={cc}&requestId={guid} | Reverse (within 24hrs) |

## GET Programs Response
```xml
<RewardPrograms>
  <Program>
    <Id>1</Id>
    <Name>Apparel21 Rewards Program</Name>
    <Description>Apparel21 Rewards Program</Description>
    <Currency>AUD</Currency>
    <Tiers Type="Array">
      <Tier>
        <Id>1</Id>
        <Name>Bronze</Name>
        <Sequence>1</Sequence>
        <AccrualSpend>1</AccrualSpend>
        <AccrualPoints>1</AccrualPoints>
        <AutoRewardPointThreshold>100</AutoRewardPointThreshold>
        <AutoRewardValue>10</AutoRewardValue>
        <MinimumSpendToRedeem>19</MinimumSpendToRedeem>
        <!-- ... expiry/demotion/qualify periods ... -->
      </Tier>
    </Tiers>
    <Gifts Type="Array">
      <Gift>
        <Id>1</Id>
        <Name>March Gifts</Name>
        <GiftStyles Type="Array">
          <Style><Id>6861</Id><Code>CAP</Code><Name>STRAW COWBOY</Name></Style>
        </GiftStyles>
      </Gift>
    </Gifts>
  </Program>
</RewardPrograms>
```

## GET Account Response
```xml
<RewardAccount>
  <Id>1</Id>
  <ProgramId>1</ProgramId>
  <ProgramName>Apparel21 Rewards Program</ProgramName>
  <Currency>AUD</Currency>
  <TierId>1</TierId>
  <TierName>Bronze</TierName>
  <PersonId>1841</PersonId>
  <AvailablePoints>100</AvailablePoints>
  <AvailableRewards>25</AvailableRewards>
  <JoinDate>2018-09-11</JoinDate>
  <PointsToNextReward>86</PointsToNextReward>
  <SpendToNextReward>86</SpendToNextReward>
  <SpendToNextTier>386</SpendToNextTier>
  <PointsList Type="Array">
    <Accrual>
      <AccrualId>42181</AccrualId>
      <Total>100</Total>
      <Redeemed>0</Redeemed>
      <Available>100</Available>
      <ExpiryDate>2019-08-22</ExpiryDate>
    </Accrual>
  </PointsList>
  <RewardsList Type="Array">
    <Reward>
      <RewardId>163421</RewardId>
      <Total>25</Total>
      <Available>25</Available>
      <ExpiryDate>2019-08-22</ExpiryDate>
      <IssueReason><Id>17665</Id><Code>Birthday Reward</Code></IssueReason>
    </Reward>
  </RewardsList>
  <GiftList Type="Array">
    <Gift>
      <IssueId>301</IssueId>
      <GiftId>21</GiftId>
      <GiftName>March Gift</GiftName>
      <Available>true</Available>
      <ExpiryDate>2019-12-05</ExpiryDate>
    </Gift>
  </GiftList>
</RewardAccount>
```

## POST Payloads

### Create Account
```xml
<RewardAccount>
  <ProgramId>21</ProgramId>
  <TierId>21</TierId>
  <TierLockedUntilDate>2019-08-22</TierLockedUntilDate>
  <PersonId>2541</PersonId>
</RewardAccount>
```

### Add Rewards
```xml
<RewardPost>
  <RequestId>5AFC78B8-A622-438B-9ABE-AE9665827838</RequestId>
  <PersonId>1841</PersonId>
  <Amount>25</Amount>
  <Description>Insta posts</Description>
  <Reference>SM1</Reference>
  <ExpiryDate>2019-08-30</ExpiryDate>
  <IssueReason><Id>17666</Id></IssueReason>
</RewardPost>
```

### Add Points
```xml
<PointsPost>
  <RequestId>{GUID}</RequestId>
  <PersonId>1841</PersonId>
  <Points>100</Points>
  <Description>Social Media Posts</Description>
  <ExpiryDate>2019-01-01</ExpiryDate>
</PointsPost>
```

### Redeem Rewards
```xml
<RewardRedemption>
  <AutoConfirm>true</AutoConfirm>
  <RequestId>{GUID}</RequestId>
  <PersonId>1841</PersonId>
  <Amount>10.50</Amount>
  <SpendAmount>19</SpendAmount>
</RewardRedemption>
```

### Redeem Gifts
```xml
<GiftRedemption>
  <AutoConfirm>false</AutoConfirm>
  <RequestId>{GUID}</RequestId>
  <PersonId>1841</PersonId>
  <GiftId>21</GiftId>
  <RedeemedSkuId>15541</RedeemedSkuId>
</GiftRedemption>
```

## Error Codes
| HTTP | Code | Text |
|------|------|------|
| 403 | 5152 | AccountId doesn't exist |
| 403 | 5155 | PersonId doesn't match AccountId |
| 403 | 5156 | Not enough points |
| 403 | 5157 | Not enough rewards |
| 403 | 5159 | TierId not valid for ProgramId |
| 403 | 5161 | Account already exists |
| 403 | 5162 | ProgramId doesn't exist |
| 403 | 5163 | RequestId already processed |
| 403 | 5164 | RequestId mandatory |
| 403 | 5171 | No gift available to redeem |
| 403 | 5173 | RedeemedSkuId invalid for GiftId |
| 403 | 5239 | Spend below minimum |

## Implementation Notes
- Complex nested resource structure under `/Rewards/`
- All POST operations require RequestId (GUID)
- AutoConfirm=false allows later confirmation/reversal
- Confirmation/Reversal are URL-parameter based (not payload)
- Person can only belong to one active rewards program

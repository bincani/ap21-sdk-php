<?php

namespace PHPAP21;

class PersonTest extends TestResource
{
    /**
     * Test POST creates a person and returns the new person ID
     * via the Location header, then GET fetches the created person.
     *
     * @return int The new person ID
     */
    public function testPostPerson()
    {
        $uniqueId = uniqid();
        $uniqueEmail = sprintf('jane.smith.%s@example.com', $uniqueId);
        $firstname = 'Jane';
        $surname = 'Smith';

        $personXml = sprintf('<Person>
  <Firstname>%s</Firstname>
  <Surname>%s</Surname>
  <Addresses>
    <Billing>
      <AddressLine1>123 Test Street</AddressLine1>
      <City>Sydney</City>
      <State>NSW</State>
      <Postcode>2000</Postcode>
      <Country>Australia</Country>
    </Billing>
  </Addresses>
  <Contacts>
    <Email>%s</Email>
  </Contacts>
</Person>', $firstname, $surname, $uniqueEmail);

        $result = static::$ap21->Person->post($personXml);

        // POST returns 201 with Location header containing the new person ID
        $this->assertEquals(201, CurlRequest::$lastHttpCode);

        // Extract person ID from Location header
        $headers = CurlRequest::$lastHttpResponseHeaders;
        $this->assertArrayHasKey('location', $headers, 'Location header missing from POST response');

        preg_match('/persons\/(\d+)/i', $headers['location'], $matches);
        $this->assertNotEmpty($matches, 'Could not extract person ID from Location header: ' . $headers['location']);

        $personId = (int) $matches[1];
        $this->assertGreaterThan(0, $personId);

        $this->summary('Person::POST (201 Created)', [[
            'personId'  => $personId,
            'firstname' => $firstname,
            'surname'   => $surname,
            'email'     => $uniqueEmail,
            'location'  => $headers['location'],
        ]]);

        return $personId;
    }

    /**
     * Test GET fetches the person created by testPostPerson
     *
     * @depends testPostPerson
     */
    public function testGetPerson(int $personId)
    {
        $person = static::$ap21->Person($personId)->get();

        $this->assertIsArray($person);
        $this->assertNotEmpty($person);
        $this->assertEquals('Jane', $person['firstname']);
        $this->assertEquals('Smith', $person['surname']);

        $this->summary('Person::GET(' . $personId . ')', [$person]);
    }

    /**
     * Test POST with invalid data triggers ApiException
     */
    public function testPostError()
    {
        $this->expectException('PHPAP21\\Exception\\ApiException');
        // Missing mandatory fields (Firstname, Surname, Email, Billing address)
        static::$ap21->Person->post(['Firstname' => '']);
    }
}

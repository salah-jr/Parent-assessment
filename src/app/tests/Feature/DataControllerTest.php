<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @test
     */
    public function itConnectsToTheEndPointAndGetsData()
    {
        $response = $this->get('/api/v1/users');
        $response->assertOk();
        $this->assertNotEmpty($response);
    }

    /**
     * @test
     */
    public function itFiltersByProviderX()
    {
        $response = $this->get('/api/v1/users?provider=DataProviderX');
        $this->assertNotEmpty($response);
        $response->assertOk()
            ->assertJsonStructure(
                [
                    '*' => [
                        'parentAmount',
                        'currency',
                        'parentEmail',
                        'statusCode',
                        'registerationDate',
                        'parentIdentification'
                    ]
                ]
            );
    }

    /**
     * @test
     */
    public function itFiltersByProviderY()
    {
        $response = $this->get('/api/v1/users?provider=DataProviderY');
        $this->assertNotEmpty($response);
        $response->assertOk()
            ->assertJsonStructure(
                [
                    '*' => [
                        'balance',
                        'currency',
                        'email',
                        'status',
                        'created_at',
                        'id'
                    ]
                ]
            );
    }

    /**
     * @test
     */
    public function itProvidesScalabilityForNewFiles()
    {
        File::copy(storage_path('json/DataProviderX.json'), storage_path('json/testFile.json'));
        $response = $this->get('/api/v1/users?provider=testFile');
        $this->assertNotEmpty($response);
        $response->assertOk();

        File::delete(storage_path('json/testFile.json'));
        $file = File::exists(storage_path('json/testFile.json'));
        $this->assertEquals(false, $file);
    }

    /**
     * @test
     */
    public function itReturnsNotFoundForInValidProviders()
    {
        $response = $this->get('/api/v1/users?provider=DataProviderZ');
        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function itFiltersByStatusCode()
    {
        $firstResponse = $this->get('/api/v1/users?statusCode=authorised');
        $secondResponse = $this->get('/api/v1/users?statusCode=decline');
        $thirdResponse = $this->get('/api/v1/users?statusCode=refunded');

        $firstResponse->assertOk()
            ->assertJsonPath('0.statusCode', 1);
       //$firstResponse->assertOk()->assertJsonPath('20.status', 100);

        $secondResponse->assertOk()
            ->assertJsonPath('0.statusCode', 2);
        // $secondResponse->assertOk()->assertJsonPath('20.status', 200);

        $thirdResponse->assertOk()
            ->assertJsonPath('0.statusCode', 3);
        //$thirdResponse->assertOk()->assertJsonPath('20.status', 300);

    }

    /**
     * @test
     */
    public function itReturnsNotFoundForInValidStatusCodes()
    {
        $response = $this->get('/api/v1/users?statusCode=abc');
        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function itFiltersByBalance()
    {
        $response = $this->get('/api/v1/users?balanceMin=1000&balanceMax=100000');
        $response->assertOk();
    }

    /**
     * @test
     */
    public function itFiltersByCurrency()
    {
        $response = $this->get('/api/v1/users?currency=EGP');
        $response->assertOk()
            ->assertSee("EGP")
            ->assertDontSee(["USD", "EUR" , "CHF"]);
    }

    /**
     * @test
     */
    public function itFiltersByAllFilters()
    {
        $firstResponse = $this->get('/api/v1/users?provider=DataProviderX&statusCode=refunded&balanceMin=1000&balanceMax=100000&currency=EGP');
        $firstResponse->assertOk();

        $secondResponse = $this->get('/api/v1/users?provider=DataProviderY&statusCode=authorised&balanceMin=1&balanceMax=900&currency=EUR');
        $secondResponse->assertOk();

        $thirdResponse = $this->get('/api/v1/users?provider=DataProviderY&statusCode=decline&balanceMin=3000&balanceMax=111100&currency=USD');
        $thirdResponse->assertOk();
    }


}

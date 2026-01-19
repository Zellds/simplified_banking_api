<?php

namespace Tests\Feature;

use App\Application\Transfer\Contracts\Clients\AuthorizationClientInterface;
use App\Application\Transfer\Contracts\Clients\NotificationClientInterface;
use App\Domain\User\UserModel;
use App\Domain\Wallet\WalletModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_transfers_money_and_returns_protocol_when_authorized(): void
    {
        $this->mock(AuthorizationClientInterface::class, function ($mock) {
            $mock->shouldReceive('authorize')->andReturn(true);
        });

        $this->mock(NotificationClientInterface::class, function ($mock) {
            $mock->shouldReceive('notify')->andReturn(true);
        });

        $payer = UserModel::create([
            'name' => 'Payer',
            'email' => 'payer@test.com',
            'document' => '11111111111',
            'type' => 'common',
        ]);

        $payee = UserModel::create([
            'name' => 'Payee',
            'email' => 'payee@test.com',
            'document' => '22222222222',
            'type' => 'merchant',
        ]);

        WalletModel::create(['user_id' => $payer->id, 'balance' => 1000]);
        WalletModel::create(['user_id' => $payee->id, 'balance' => 0]);

        $response = $this->postJson('/api/transfer', [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => 100,
        ]);

        $response->assertCreated();
        $response->assertJsonStructure(['protocol']);

        $this->assertDatabaseHas('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $payer->id,
            'balance' => '900.00',
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $payee->id,
            'balance' => '100.00',
        ]);
    }

    public function test_it_rejects_transfer_when_authorizer_denies(): void
    {
        $this->mock(AuthorizationClientInterface::class, function ($mock) {
            $mock->shouldReceive('authorize')->andReturn(false);
        });

        $this->mock(NotificationClientInterface::class, function ($mock) {
            $mock->shouldReceive('notify')->andReturn(true);
        });

        $payer = UserModel::create([
            'name' => 'Payer',
            'email' => 'payer@test.com',
            'document' => '11111111111',
            'type' => 'common',
        ]);

        $payee = UserModel::create([
            'name' => 'Payee',
            'email' => 'payee@test.com',
            'document' => '22222222222',
            'type' => 'merchant',
        ]);

        WalletModel::create(['user_id' => $payer->id, 'balance' => 1000]);
        WalletModel::create(['user_id' => $payee->id, 'balance' => 0]);

        $response = $this->postJson('/api/transfer', [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => 100,
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('wallets', ['user_id' => $payer->id, 'balance' => '1000.00']);
        $this->assertDatabaseHas('wallets', ['user_id' => $payee->id, 'balance' => '0.00']);
    }
}

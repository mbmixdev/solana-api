<?php

namespace Tighten\SolanaPhpSdk;

use Tighten\SolanaPhpSdk\Exceptions\AccountNotFoundException;
use Tighten\SolanaPhpSdk\Util\Commitment;

class Connection extends Program
{
    /**
     * @param string $pubKey
     * @return array
     */
    public function getAccountInfo(string $pubKey): array
    {
        $accountResponse = $this->client->call('getAccountInfo', [$pubKey, ["encoding" => "jsonParsed"]])['value'];

        if (! $accountResponse) {
            throw new AccountNotFoundException("API Error: Account {$pubKey} not found.");
        }

        return $accountResponse;
    }

    /**
     * @param string $pubKey
     * @return float
     */
    public function getBalance(string $pubKey): float
    {
        return $this->client->call('getBalance', [$pubKey])['value'];
    }

    /**
     * @param string $transactionSignature
     * @return array
     */
    public function getConfirmedTransaction(string $transactionSignature): array
    {
        return $this->client->call('getConfirmedTransaction', [$transactionSignature]);
    }

    /**
     * NEW: This method is only available in solana-core v1.7 or newer. Please use getConfirmedTransaction for solana-core v1.6
     *
     * @param string $transactionSignature
     * @return array
     */
    public function getTransaction(string $transactionSignature): ?array
    {
        return $this->client->call('getTransaction', [$transactionSignature]);
    }

    /**
     * @param Commitment|null $commitment
     * @return array
     * @throws Exceptions\GenericException|Exceptions\MethodNotFoundException|Exceptions\InvalidIdResponseException
     */
    public function getRecentBlockhash(?Commitment $commitment = null): array
    {
        return $this->client->call('getRecentBlockhash', array_filter([$commitment]))['value'];
    }


    /**
     * @param Commitment|null $commitment
     * @return array
     * @throws Exceptions\GenericException|Exceptions\MethodNotFoundException|Exceptions\InvalidIdResponseException
     */
    public function getBlockHeight()
    {
        return $this->client->call('getBlockHeight');
    }

    /**
     * @param Commitment|null $commitment
     * @return array
     * @throws Exceptions\GenericException|Exceptions\MethodNotFoundException|Exceptions\InvalidIdResponseException
     */
    public function getBlockBySlotNumber(int $blockNumber, string $encoding = "base64", string $transactionDetails = 'full')
    {
        return $this->client->call('getBlock', [$blockNumber,
          [
            "encoding" => $encoding,
            "transactionDetails" => $transactionDetails,
            "rewards" => false
          ]
        ]);
    }

/**
     * @param Commitment|null $commitment
     * @return array
     * @throws Exceptions\GenericException|Exceptions\MethodNotFoundException|Exceptions\InvalidIdResponseException
     */
    public function getLatestBlockhash(string $commitment = 'processed')
    {
        return $this->client->call('getLatestBlockhash', [
          [
            "commitment" => $commitment,
          ]
        ]);
    }

    /**
     * @param Commitment|null $commitment
     * @return array
     * @throws Exceptions\GenericException|Exceptions\MethodNotFoundException|Exceptions\InvalidIdResponseException
     */
    public function getBlocks(int $slot)
    {
        return $this->client->call('getBlocks', [$slot]);
    }


    public function getBlocksWithLimit(int $slot)
    {
        $limit = 10;
        return $this->client->call('getBlocksWithLimit', [$slot, $limit]);
    }

    /**
     * @param Transaction $transaction
     * @param Keypair[] $signers
     * @param array $params
     * @return array|\Illuminate\Http\Client\Response
     * @throws Exceptions\GenericException
     * @throws Exceptions\InvalidIdResponseException
     * @throws Exceptions\MethodNotFoundException
     */
    public function sendTransaction(Transaction $transaction, array $signers, array $params = [])
    {
        if (! $transaction->recentBlockhash) {
            $transaction->recentBlockhash = $this->getRecentBlockhash()['blockhash'];
        }

        $transaction->sign(...$signers);

        $rawBinaryString = $transaction->serialize(false);

        $hashString = sodium_bin2base64($rawBinaryString, SODIUM_BASE64_VARIANT_ORIGINAL);

        return $this->client->call('sendTransaction', [
            $hashString,
            [
                'encoding' => 'base64',
                'preflightCommitment' => 'confirmed',
            ],
        ]);
    }


  /**
   * @param string $message
   * @return float
   * @throws Exceptions\GenericException
   * @throws Exceptions\InvalidIdResponseException
   * @throws Exceptions\MethodNotFoundException
   * @throws \SodiumException
   */
  public function getFeeForMessage(string $message, string $commitment = 'processed')
    {
        return $this->client->call('getFeeForMessage', [
          $message,
            [
                'commitment' => $commitment
            ],
        ]);
    }
}

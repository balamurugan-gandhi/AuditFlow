<?php

namespace App\Services;

use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

class ClientService
{
    protected $clientRepository;

    public function __construct(ClientRepositoryInterface $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function getAllClients(?\App\Models\User $user = null): Collection
    {
        if ($user) {
            return $this->clientRepository->allForUser($user);
        }
        return $this->clientRepository->all();
    }

    public function getClientById(int $id): ?Client
    {
        return $this->clientRepository->find($id);
    }

    public function createClient(array $data): Client
    {
        return $this->clientRepository->create($data);
    }

    public function updateClient(int $id, array $data): bool
    {
        return $this->clientRepository->update($id, $data);
    }

    public function deleteClient(int $id): bool
    {
        return $this->clientRepository->delete($id);
    }
}

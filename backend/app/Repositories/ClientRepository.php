<?php

namespace App\Repositories;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ClientRepository implements ClientRepositoryInterface
{
    public function all(): Collection
    {
        return Client::orderBy('created_at', 'desc')->get();
    }

    public function allForUser(\App\Models\User $user): Collection
    {
        return Client::forUser($user)->orderBy('created_at', 'desc')->get();
    }

    public function find(int $id): ?Client
    {
        return Client::with(['files.assignee'])->find($id);
    }

    public function create(array $data): Client
    {
        return Client::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $client = Client::find($id);
        if (!$client) {
            return false;
        }
        return $client->update($data);
    }

    public function delete(int $id): bool
    {
        $client = Client::find($id);
        if (!$client) {
            return false;
        }
        return $client->delete();
    }
}

<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    // Qualquer usuário logado pode ver a listagem
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Só o dono da task pode ver ela
    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    // Qualquer usuário logado pode criar
    public function create(User $user): bool
    {
        return true; // false para teste
    }

    // Só o dono pode editar
    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    // Só o dono pode deletar
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
}

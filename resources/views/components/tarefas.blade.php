<?php

use Livewire\Component;

new class extends Component
{
    public string $novaTarefa = '';
    public array $tarefas = [];

    public function adicionar(): void
    {
        if (strlen($this->novaTarefa) === 0) {
            return;
        }

        $this->tarefas[] = ['texto' => $this->novaTarefa, 'concluida' => false];
        $this->novaTarefa = '';
    }

    public function remover(int $index): void
    {
        array_splice($this->tarefas, $index, 1);
    }

    public function concluir(int $index): void
    {
        $this->tarefas[$index]['concluida'] = !$this->tarefas[$index]['concluida'];
    }
};
?>

<div>
    <h2>Tarefas</h2>

    <form wire:submit="adicionar">
        <input wire:model="novaTarefa" type="text" placeholder="Nova tarefa...">
        <button type="submit">Salvar</button>
    </form>

    <ul>
        @foreach ($tarefas as $index => $tarefa)
            <li>
                <span style="{{ $tarefa['concluida'] ? 'text-decoration: line-through' : '' }}">
                    {{ $tarefa['texto'] }}
                </span>
                <button wire:click="concluir({{ $index }})">Concluir</button>
                <button wire:click="remover({{ $index }})">Remover</button>
            </li>
        @endforeach
    </ul>
</div>

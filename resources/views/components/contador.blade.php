<?php

use Livewire\Component;

new class extends Component
{
    public int $valor = 0;

    public function incrementar(): void
    {
        $this->valor++;
    }

    public function decrementar(): void
    {
        $this->valor--;
    }

    public function zerar(): void
    {
        $this->valor = 0;
    }
};
?>

<div>
    <h2>Contador: {{ $valor }}</h2>

    <button wire:click="incrementar">+1</button>
    <button wire:click="decrementar">-1</button>
    <button wire:click="zerar">Zerar</button>
</div>

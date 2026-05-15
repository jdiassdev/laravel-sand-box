# sand-box

Projeto Laravel usado para testar bibliotecas e ferramentas de forma isolada.

## Branches

Cada branch testa uma lib ou ferramenta específica e segue a convenção de nome `test-<nome>` (ex: `test-inertia`, `test-livewire`).

- Branches de teste nunca são mergeadas na `master`
- A `master` existe apenas como base limpa para novos experimentos
- Cada branch é tratada como um experimento isolado e descartável

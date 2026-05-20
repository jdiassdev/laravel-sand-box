<script setup>
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    tasks: Array,
    filter: String,
})

const form = useForm({
    name: '',
    category: 'personal',
})

function submit() {
    form.post('/tasks', { onSuccess: () => form.reset() })
}

function toggleComplete(task) {
    router.patch(`/tasks/${task.id}`, { completed: !task.completed })
}

function remove(task) {
    router.delete(`/tasks/${task.id}`)
}

function setFilter(value) {
    router.get('/tasks', value ? { filter: value } : {}, { preserveState: true })
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 py-10 px-4">
        <div class="max-w-xl mx-auto space-y-6">

            <h1 class="text-2xl font-bold text-gray-800">Tarefas</h1>

            <!-- Formulário -->
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow p-4 flex gap-2">
                <input v-model="form.name" type="text" placeholder="Nova tarefa..."
                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                <select v-model="form.category"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="personal">Personal</option>
                    <option value="work">Work</option>
                    <option value="study">Study</option>
                </select>
                <button type="submit" :disabled="form.processing || !form.name"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
                    Adicionar
                </button>
            </form>

            <!-- Filtros -->
            <div class="flex gap-2">
                <button
                    v-for="f in [{ label: 'Todas', value: null }, { label: 'Pendentes', value: 'pending' }, { label: 'Concluídas', value: 'completed' }]"
                    :key="f.value" @click="setFilter(f.value)" :class="[
                        'px-3 py-1 rounded-full text-sm font-medium transition',
                        filter === f.value || (f.value === null && !filter)
                            ? 'bg-blue-600 text-white'
                            : 'bg-white text-gray-600 hover:bg-gray-200'
                    ]">
                    {{ f.label }}
                </button>
            </div>

            <!-- Lista -->
            <div class="space-y-2">
                <p v-if="tasks.length === 0" class="text-center text-gray-400 text-sm py-8">
                    Nenhuma tarefa encontrada.
                </p>

                <div v-for="task in tasks" :key="task.id"
                    class="bg-white rounded-xl shadow px-4 py-3 flex items-center gap-3 group">
                    <button @click="toggleComplete(task)"
                        :title="task.completed ? 'Marcar como pendente' : 'Marcar como concluída'" :class="[
                            'shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition cursor-pointer',
                            task.completed
                                ? 'bg-green-500 border-green-500'
                                : 'border-gray-300 hover:border-green-400 hover:bg-green-50'
                        ]">
                        <svg v-if="task.completed" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="w-3 h-3 text-green-400 opacity-0 group-hover:opacity-100 transition"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>

                    <div class="flex-1 min-w-0">
                        <p
                            :class="['text-sm font-medium truncate', task.completed ? 'line-through text-gray-400' : 'text-gray-800']">
                            {{ task.name }}
                        </p>
                        <span :class="['text-xs', task.completed ? 'text-gray-300' : 'text-gray-400']">{{ task.category
                            }}</span>
                    </div>

                    <button @click="remove(task)" title="Remover tarefa"
                        class="text-gray-200 hover:text-red-400 transition shrink-0 opacity-0 group-hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

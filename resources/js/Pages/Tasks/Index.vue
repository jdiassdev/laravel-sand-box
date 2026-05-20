<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm, router, usePage } from '@inertiajs/vue3'

defineProps({
    tasks: Array,
})

const { props } = usePage()
const can = props.auth.can

const form = useForm({ title: '' })

function submit() {
    form.post(route('tasks.index'), { onSuccess: () => form.reset() })
}

function toggleComplete(task) {
    router.patch(route('tasks.update', task.id), { completed: !task.completed })
}

function remove(task) {
    router.delete(route('tasks.destroy', task.id))
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>Minhas Tarefas</template>

        <div class="py-8">
            <div class="max-w-xl mx-auto px-4 space-y-6">

                <!-- Formulário — só aparece se can.create_task for true -->
                <form v-if="can.create_task" @submit.prevent="submit" class="bg-white rounded-xl shadow p-4 flex gap-2">
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="Nova tarefa..."
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <button
                        type="submit"
                        :disabled="form.processing || !form.title"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
                    >
                        Adicionar
                    </button>
                </form>

                <!-- Lista -->
                <div class="space-y-2">
                    <p v-if="tasks.length === 0" class="text-center text-gray-400 text-sm py-8">
                        Nenhuma tarefa ainda.
                    </p>

                    <div
                        v-for="task in tasks"
                        :key="task.id"
                        class="bg-white rounded-xl shadow px-4 py-3 flex items-center gap-3 group"
                    >
                        <button
                            @click="toggleComplete(task)"
                            :title="task.completed ? 'Marcar como pendente' : 'Marcar como concluída'"
                            :class="[
                                'shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition cursor-pointer',
                                task.completed
                                    ? 'bg-green-500 border-green-500'
                                    : 'border-gray-300 hover:border-green-400 hover:bg-green-50'
                            ]"
                        >
                            <svg v-if="task.completed" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg v-else class="w-3 h-3 text-green-400 opacity-0 group-hover:opacity-100 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>

                        <span :class="['flex-1 text-sm font-medium', task.completed ? 'line-through text-gray-400' : 'text-gray-800']">
                            {{ task.title }}
                        </span>

                        <button
                            @click="remove(task)"
                            title="Remover"
                            class="text-gray-200 hover:text-red-400 transition shrink-0 opacity-0 group-hover:opacity-100"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

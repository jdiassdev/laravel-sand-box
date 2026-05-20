<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    message: String,
})
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-800">{{ message }}</h1>
            <p class="mt-4 text-gray-500">Laravel + Inertia.js + Vue 3</p>
            <Link href="/tasks" class="mt-6 inline-block bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                Ver tarefas
            </Link>
        </div>
    </div>
</template>

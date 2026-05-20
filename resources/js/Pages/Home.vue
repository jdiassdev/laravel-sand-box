<script setup>
defineProps({
    message: String,
})
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-800">{{ message }}</h1>
            <p class="mt-4 text-gray-500">Laravel + Inertia.js + Vue 3</p>
        </div>
    </div>
</template>

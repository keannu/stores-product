<template>
    <div class="p-4">
        <h1 class="text-xl font-bold">{{ message }}</h1>
        <button @click="count++" class="px-3 py-1 bg-blue-500 text-white rounded">
            Clicked {{ count }} times
        </button>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const message = ref('Hello from Vue inside Laravel!');
const count = ref(0);
</script>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'

const props = defineProps({
    durationMinutes: {
        type: Number,
        default: 30
    },
    absoluteEndTime: {
        type: String, // ISO string or any format Date can parse
        default: null
    }
})

const emit = defineEmits(['expired'])

const calculateTimeLeft = () => {
    const durationSeconds = props.durationMinutes * 60;
    if (props.absoluteEndTime) {
        const globalRemaining = Math.max(0, Math.floor((new Date(props.absoluteEndTime) - new Date()) / 1000));
        return Math.min(durationSeconds, globalRemaining);
    }
    return durationSeconds;
};

const timeLeft = ref(calculateTimeLeft())
let timerInterval = null

const formattedTime = computed(() => {
    const hours = Math.floor(timeLeft.value / 3600)
    const minutes = Math.floor((timeLeft.value % 3600) / 60)
    const seconds = timeLeft.value % 60
    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
})

const isLowTime = computed(() => timeLeft.value <= 300) // 5 minutes

onMounted(() => {
    timerInterval = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--
        } else {
            clearInterval(timerInterval)
            emit('expired')
        }
    }, 1000)
})

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval)
})
</script>

<template>
    <div 
        class="text-2xl font-mono font-black px-6 py-3 rounded-2xl transition-all border backdrop-blur-md shadow-lg"
        :class="[
            isLowTime 
                ? 'text-red-500 bg-red-500/10 border-red-500/30 animate-pulse shadow-red-900/20' 
                : 'text-blue-400 bg-blue-500/10 border-blue-500/20 shadow-blue-900/10'
        ]"
    >
        {{ formattedTime }}
    </div>
</template>

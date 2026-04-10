<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { 
    CheckCircleIcon, 
    XCircleIcon,
    ArrowPathIcon,
    HomeIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    score: Number,
    total: Number,
    feedback: Array,
    exam: Object
})

const percentage = Math.round((props.score / props.total) * 100)
</script>

<template>
    <Head title="Résultats Entraînement" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-8 px-4">
            <header class="text-center mb-12">
                <div class="inline-flex items-center gap-2 mb-4 px-4 py-2 bg-gray-900 text-white rounded-2xl">
                    <CheckBadgeIcon class="h-5 w-5 text-blue-400" />
                    <span class="text-xs font-black uppercase tracking-widest">Correction Immédiate</span>
                </div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight mb-2">{{ exam.titre }}</h1>
                <p class="text-gray-500 font-bold">Analyse de vos performances en mode libre</p>
            </header>

            <div class="bg-white rounded-[3rem] shadow-sm border border-gray-100 overflow-hidden mb-12">
                <!-- Score Banner -->
                <div class="p-10 bg-gray-900 text-white flex flex-col items-center">
                    <div class="h-32 w-32 rounded-full border-8 border-white/10 flex items-center justify-center mb-4">
                        <span class="text-4xl font-black">{{ percentage }}%</span>
                    </div>
                    <p class="text-xl font-bold opacity-80">{{ score }} / {{ total }} points</p>
                </div>

                <!-- Detailed Feedback -->
                <div class="p-8 sm:p-12 space-y-8">
                    <div v-for="(item, idx) in feedback" :key="idx" class="p-6 rounded-3xl border-2" :class="item.is_correct ? 'border-green-100 bg-green-50/30' : 'border-red-100 bg-red-50/30'">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-black uppercase tracking-widest" :class="item.is_correct ? 'text-green-600' : 'text-red-600'">
                                Question {{ idx + 1 }} • {{ item.is_correct ? 'Correct' : 'Incorrect' }}
                            </span>
                            <CheckCircleIcon v-if="item.is_correct" class="h-6 w-6 text-green-600" />
                            <XCircleIcon v-else class="h-6 w-6 text-red-600" />
                        </div>
                        
                        <p class="font-bold text-gray-900 text-lg mb-4">
                            {{ exam.questions.find(q => q.id === item.question_id).question_text }}
                        </p>

                        <div class="p-4 bg-white/60 rounded-2xl border border-white">
                            <p class="text-sm">
                                <span class="font-black" :class="item.is_correct ? 'text-green-700' : 'text-red-700'">Explication : </span>
                                <span class="text-gray-600 font-medium">{{ item.explanation }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <Link :href="route('exams.show', exam.id)" class="px-8 py-4 bg-black text-white rounded-2xl font-black flex items-center justify-center gap-3 hover:scale-105 transition">
                    <ArrowPathIcon class="h-5 w-5" />
                    Réessayer le Quiz
                </Link>
                <Link :href="route('student.dashboard')" class="px-8 py-4 bg-white text-gray-900 border-2 border-gray-100 rounded-2xl font-black flex items-center justify-center gap-3 hover:bg-gray-50 transition">
                    <HomeIcon class="h-5 w-5" />
                    Retour au Dashboard
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { CheckBadgeIcon } from '@heroicons/vue/24/solid';
export default { components: { CheckBadgeIcon } }
</script>

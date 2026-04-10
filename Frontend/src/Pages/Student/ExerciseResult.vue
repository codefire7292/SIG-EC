<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import {
    BeakerIcon,
    CheckCircleIcon,
    XCircleIcon,
    ChevronLeftIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline'
import { computed } from 'vue'

const props = defineProps({
    submission: Object, // Includes chapter.questions.options and answers
})

const exercise = computed(() => props.submission.chapter)
const answers = computed(() => props.submission.answers || {})

const getOptionText = (question, optionId) => {
    return question.options.find(o => o.id === Number(optionId))?.texte || optionId
}

// Function to determine if a question was answered correctly
const isCorrect = (question) => {
    if (question.type !== 'qcm') return null
    const submittedId = answers.value[question.id]
    const correctOption = question.options.find(o => o.is_correct)
    return submittedId && correctOption && Number(submittedId) === correctOption.id
}

const getCorrectOption = (question) => {
    return question.options.find(o => o.is_correct)
}
</script>

<template>
    <Head :title="'Résultat : ' + (exercise.exercise_title || exercise.titre)" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

            <!-- Back navigation -->
            <Link :href="route('student.exercises.index')" class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase tracking-widest mb-8 hover:text-blue-600 transition">
                <ChevronLeftIcon class="h-4 w-4" />
                Retour aux exercices
            </Link>

            <!-- Header -->
            <header class="mb-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center shadow-sm border border-blue-100">
                            <BeakerIcon class="h-6 w-6" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-black text-gray-900 tracking-tight">{{ exercise.exercise_title || exercise.titre }}</h1>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ exercise.module?.titre }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-black text-blue-600 tracking-tighter">{{ submission.grade || 0 }}<span class="text-xs text-gray-300 ml-1">/ {{ exercise.exercise_points }}</span></div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Note obtenue</p>
                    </div>
                </div>

                <div v-if="submission.trainer_feedback" class="p-6 bg-green-50 rounded-3xl border border-green-100 text-sm text-green-900 font-medium relative mt-6">
                    <InformationCircleIcon class="h-5 w-5 text-green-500 absolute -top-2.5 -left-2.5 bg-white rounded-full" />
                    <p class="text-[9px] font-black text-green-500 uppercase tracking-widest mb-1.5 opacity-60">Retour du formateur</p>
                    {{ submission.trainer_feedback }}
                </div>
            </header>

            <!-- Questions Breakdown -->
            <div class="space-y-8">
                <div
                    v-for="(question, idx) in exercise.questions"
                    :key="question.id"
                    class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden relative"
                >
                    <!-- Correction Badge (Top Right) -->
                    <div v-if="question.type === 'qcm'" class="absolute top-6 right-8">
                        <div v-if="isCorrect(question)" class="flex items-center gap-1.5 text-green-600 font-black text-[10px] uppercase tracking-widest bg-green-50 px-3 py-1.5 rounded-full border border-green-100">
                            <CheckCircleIcon class="h-3.5 w-3.5" />
                            Correct
                        </div>
                        <div v-else class="flex items-center gap-1.5 text-red-500 font-black text-[10px] uppercase tracking-widest bg-red-50 px-3 py-1.5 rounded-full border border-red-100">
                            <XCircleIcon class="h-3.5 w-3.5" />
                            Incorrect
                        </div>
                    </div>

                    <!-- Question Header -->
                    <div class="px-8 pt-8 pb-4 flex items-start justify-between">
                        <div class="flex items-start gap-4 flex-1">
                            <span class="h-8 w-8 bg-gray-900 text-white rounded-xl flex items-center justify-center font-black text-sm shrink-0 mt-0.5">{{ idx + 1 }}</span>
                            <p class="font-black text-gray-900 text-lg tracking-tight leading-snug pr-24">{{ question.enonce }}</p>
                        </div>
                    </div>

                    <!-- QCM Options Display -->
                    <div v-if="question.type === 'qcm'" class="px-8 pb-8 space-y-3 mt-2">
                        <div
                            v-for="option in question.options"
                            :key="option.id"
                            class="flex items-center gap-4 p-4 rounded-2xl border transition-all"
                            :class="[
                                option.is_correct ? 'border-green-500 bg-green-50/50' : 'border-gray-100',
                                answers[question.id] == option.id && !option.is_correct ? 'border-red-500 bg-red-50/50' : ''
                            ]"
                        >
                            <div class="h-5 w-5 rounded-full border-2 flex items-center justify-center shrink-0"
                                :class="[
                                    option.is_correct ? 'border-green-500 bg-green-500 text-white' : 'border-gray-200',
                                    answers[question.id] == option.id && !option.is_correct ? 'border-red-500 bg-red-500 text-white' : ''
                                ]"
                            >
                                <CheckCircleIcon v-if="option.is_correct" class="h-4 w-4" />
                                <XCircleIcon v-else-if="answers[question.id] == option.id" class="h-4 w-4" />
                            </div>
                            <span class="font-bold shrink-0" :class="option.is_correct ? 'text-green-800' : (answers[question.id] == option.id ? 'text-red-800' : 'text-gray-500')">
                                {{ option.texte }}
                            </span>
                            
                            <span v-if="answers[question.id] == option.id" class="text-[9px] font-black uppercase tracking-widest ml-auto" :class="option.is_correct ? 'text-green-500' : 'text-red-500'">
                                Votre réponse
                            </span>
                        </div>
                    </div>

                    <!-- Open Question Display -->
                    <div v-else class="px-8 pb-8 mt-2">
                        <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100 italic text-sm text-gray-700 font-medium">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2 not-italic">Votre réponse :</p>
                            {{ answers[question.id] || '(Aucune réponse)' }}
                        </div>
                    </div>
                </div>

                <div class="pt-10 flex justify-center">
                    <Link
                        :href="route('student.exercises.index')"
                        class="px-10 py-4 bg-gray-900 text-white rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-blue-600 transition shadow-xl shadow-gray-100"
                    >
                        Terminer la revue
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

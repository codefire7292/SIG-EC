<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { BeakerIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    exercise: Object, // Chapter with questions.options
})

// Build reactive answers map: { [question_id]: answer }
const answers = ref({})
props.exercise.questions?.forEach(q => {
    answers.value[q.id] = q.type === 'qcm' ? null : ''
})

const allAnswered = computed(() => {
    return props.exercise.questions?.every(q => {
        const a = answers.value[q.id]
        return a !== null && a !== ''
    })
})

const form = useForm({})
const submitted = ref(false)

const submit = () => {
    form.transform(() => ({
        answers: answers.value,
        type: 'online',
    })).post(route('student.exercises.submit', props.exercise.id), {
        onSuccess: () => { submitted.value = true }
    })
}
</script>

<template>
    <Head :title="exercise.exercise_title || exercise.titre" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <header class="mb-10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-12 w-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center shadow-sm border border-blue-100">
                        <BeakerIcon class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight">{{ exercise.exercise_title || exercise.titre }}</h1>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ exercise.module?.titre }} • {{ exercise.exercise_points }} pts</p>
                    </div>
                </div>
                <div v-if="exercise.exercise_instructions" class="p-5 bg-blue-50/40 rounded-3xl border border-blue-100 text-sm text-blue-900 font-medium italic">
                    {{ exercise.exercise_instructions }}
                </div>
            </header>

            <!-- Success State -->
            <div v-if="submitted" class="py-20 text-center bg-green-50 rounded-[3rem] border border-green-100">
                <CheckCircleIcon class="h-16 w-16 text-green-500 mx-auto mb-4" />
                <h2 class="text-2xl font-black text-green-800 tracking-tight">Exercice soumis !</h2>
                <p class="text-sm text-green-600 font-medium mt-2">Votre réponse a été enregistrée. Le formateur la corrigera prochainement.</p>
            </div>

            <!-- Quiz Form -->
            <div v-else class="space-y-8">
                <div
                    v-for="(question, idx) in exercise.questions"
                    :key="question.id"
                    class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden"
                >
                    <!-- Question Header -->
                    <div class="px-8 pt-8 pb-4 flex items-start justify-between">
                        <div class="flex items-start gap-4 flex-1">
                            <span class="h-8 w-8 bg-blue-600 text-white rounded-xl flex items-center justify-center font-black text-sm shrink-0 mt-0.5">{{ idx + 1 }}</span>
                            <p class="font-black text-gray-900 text-lg tracking-tight leading-snug">{{ question.enonce }}</p>
                        </div>
                        <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-full ml-4 shrink-0 border border-blue-100">{{ question.points }} pts</span>
                    </div>

                    <!-- QCM Options -->
                    <div v-if="question.type === 'qcm'" class="px-8 pb-8 space-y-3">
                        <label
                            v-for="option in question.options"
                            :key="option.id"
                            class="flex items-center gap-4 p-4 rounded-2xl border cursor-pointer transition-all"
                            :class="answers[question.id] === option.id
                                ? 'border-blue-500 bg-blue-50 shadow-sm shadow-blue-100'
                                : 'border-gray-100 hover:border-blue-200 hover:bg-blue-50/30'"
                        >
                            <input
                                type="radio"
                                :name="'q_' + question.id"
                                :value="option.id"
                                v-model="answers[question.id]"
                                class="accent-blue-600 w-4 h-4"
                            />
                            <span class="font-bold text-gray-800">{{ option.texte }}</span>
                        </label>
                    </div>

                    <!-- Open Question -->
                    <div v-else class="px-8 pb-8">
                        <textarea
                            v-model="answers[question.id]"
                            rows="4"
                            placeholder="Rédigez votre réponse ici..."
                            class="w-full bg-gray-50 border-0 rounded-2xl focus:ring-2 focus:ring-blue-500 font-medium px-5 py-4 text-sm resize-none"
                        ></textarea>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="!exercise.questions?.length" class="py-20 text-center text-gray-300 font-bold italic bg-white rounded-[2.5rem] border border-dashed border-gray-200">
                    Aucune question n'a encore été ajoutée à cet exercice.
                </div>

                <!-- Submit -->
                <div v-if="exercise.questions?.length" class="flex justify-end pt-4">
                    <button
                        @click="submit"
                        :disabled="form.processing || !allAnswered"
                        class="px-10 py-4 bg-blue-600 text-white rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-blue-700 transition shadow-xl shadow-blue-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Envoi…' : 'Soumettre l\'exercice' }}
                    </button>
                </div>

                <p v-if="!allAnswered && exercise.questions?.length" class="text-center text-xs text-gray-400 font-bold italic">
                    Répondez à toutes les questions pour pouvoir soumettre.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

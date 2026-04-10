<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import {
    PencilSquareIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    AcademicCapIcon,
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    exams: Array,
})

const statusLabel = (exam) => {
    if (!exam.my_result) return 'À faire'
    return `${exam.my_result.score?.toFixed(1)} / 20`
}

const statusClass = (exam) => {
    if (!exam.my_result) return 'bg-amber-50 text-amber-600 border-amber-100'
    const score = exam.my_result.score
    if (score >= 14) return 'bg-green-50 text-green-600 border-green-100'
    if (score >= 10) return 'bg-blue-50 text-blue-600 border-blue-100'
    return 'bg-red-50 text-red-600 border-red-100'
}

const formatDateTime = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleString('fr-FR', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatTime = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

<template>
    <Head title="Mes Examens" />
    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <header class="mb-10">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                    <PencilSquareIcon class="h-10 w-10 text-red-500" />
                    Mes Examens
                </h1>
                <p class="mt-2 text-gray-500 font-medium">Consultez vos examens et résultats.</p>
            </header>

            <div class="space-y-4">
                <div
                    v-for="exam in exams"
                    :key="exam.id"
                    class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 flex items-center justify-between hover:border-blue-200 transition"
                >
                    <div class="flex items-center gap-5">
                        <div class="h-14 w-14 rounded-2xl flex items-center justify-center shadow-sm border border-gray-100"
                            :class="exam.my_result ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-500'"
                        >
                            <CheckCircleIcon v-if="exam.my_result" class="h-7 w-7" />
                            <ClockIcon v-else class="h-7 w-7" />
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900 text-lg tracking-tight">{{ exam.titre }}</h3>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                    {{ exam.module?.titre }} •
                                    {{ exam.is_practice ? 'Entraînement' : 'Examen' }} •
                                    {{ exam.total_points }} pts
                                </p>
                                <div v-if="exam.scheduled_at" class="flex items-center gap-1.5 px-2 py-0.5 bg-blue-50 text-blue-500 rounded-md border border-blue-100 text-[10px] font-black uppercase tracking-tight">
                                    <ClockIcon class="h-3 w-3" />
                                    {{ formatDateTime(exam.scheduled_at) }} - {{ formatTime(exam.end_at) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Énoncé pour examen sur table -->
                        <a 
                            v-if="!exam.is_online && exam.document_path"
                            :href="'/storage/' + exam.document_path"
                            target="_blank"
                            class="px-4 py-2 bg-purple-100 text-purple-700 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-purple-200 transition flex items-center gap-2 border border-purple-200"
                        >
                            <ArrowDownTrayIcon class="h-3.5 w-3.5" />
                            Énoncé
                        </a>

                        <span v-if="!exam.is_online" class="px-3 py-1 bg-gray-100 text-gray-500 text-[9px] font-black uppercase tracking-tighter rounded-md border border-gray-200">
                            Sur Table
                        </span>

                        <!-- État de disponibilité (Online) -->
                        <template v-if="exam.is_online">
                            <span v-if="exam.has_ended && !exam.my_result" class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border bg-red-50 text-red-400 border-red-100 italic">
                                Terminé
                            </span>
                            <span v-else-if="!exam.can_start && !exam.my_result" class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border bg-blue-50 text-blue-500 border-blue-100 italic animate-pulse">
                                Bientôt
                            </span>
                        </template>

                        <span v-if="exam.my_result" class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border" :class="statusClass(exam)">
                            {{ statusLabel(exam) }}
                        </span>

                        <Link
                            v-if="exam.can_start && (!exam.my_result || exam.is_practice)"
                            :href="route('student.exams.show', exam.id)"
                            class="px-5 py-2.5 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition shadow-lg shadow-gray-200"
                        >
                            {{ exam.my_result ? 'Refaire' : 'Commencer' }}
                        </Link>
                    </div>
                </div>

                <div v-if="exams.length === 0" class="py-20 text-center text-gray-400 font-bold italic bg-white rounded-[2.5rem] border border-dashed border-gray-200">
                    <AcademicCapIcon class="h-12 w-12 mx-auto mb-4 text-gray-200" />
                    Aucun examen disponible pour le moment.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

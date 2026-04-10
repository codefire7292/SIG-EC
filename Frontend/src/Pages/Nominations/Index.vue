<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { 
    CheckBadgeIcon, 
    XCircleIcon, 
    UserIcon, 
    UserGroupIcon,
    AcademicCapIcon,
    ClockIcon,
    CheckCircleIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    nominations: Object
})

const form = useForm({
    decision: ''
})

const processNomination = (id, decision) => {
    form.decision = decision
    form.patch(route('nominations.approve', id), {
        preserveScroll: true
    })
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

<template>
    <Head title="Validations - Nominations" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-10">
                <h2 class="text-3xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                    <CheckBadgeIcon class="h-10 w-10 text-blue-600" />
                    Centre de Validations
                </h2>
                <p class="text-gray-500 font-medium mt-2">Gérez les nominations des responsables de groupes proposées par les formateurs.</p>
            </div>

            <!-- Nominations List -->
            <div class="space-y-6">
                <div v-if="nominations.data.length > 0" class="grid grid-cols-1 gap-4">
                    <div v-for="nomination in nominations.data" :key="nomination.id" 
                        class="bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition-all p-6 flex flex-col md:flex-row md:items-center justify-between gap-6"
                    >
                        <div class="flex items-center gap-5">
                            <div class="h-16 w-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 relative overflow-hidden shrink-0">
                                <UserIcon class="h-9 w-9" />
                                <div class="absolute bottom-0 inset-x-0 h-1 bg-blue-600"></div>
                            </div>
                            
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-lg font-black text-gray-900">{{ nomination.user.name }}</h3>
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-md border border-blue-100">
                                        Candidat
                                    </span>
                                    <span 
                                        class="px-2 py-0.5 text-[10px] font-black uppercase tracking-widest rounded-md border"
                                        :class="nomination.role === 'responsable' 
                                            ? 'bg-blue-600 text-white border-blue-600 shadow-sm' 
                                            : 'bg-amber-500 text-white border-amber-500 shadow-sm'"
                                    >
                                        {{ nomination.role === 'responsable' ? 'Chef de Groupe' : 'Adjoint' }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500 font-medium">
                                    <span class="flex items-center gap-1.5">
                                        <UserGroupIcon class="h-4 w-4 text-gray-400" />
                                        Groupe: <span class="text-gray-900 font-bold ml-0.5">{{ nomination.group.nom_groupe }}</span>
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <AcademicCapIcon class="h-4 w-4 text-gray-400" />
                                        Module: <span class="text-gray-900 font-bold ml-0.5">{{ nomination.group.module?.titre }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <!-- Nominator Info -->
                            <div class="hidden lg:block text-right border-l pl-6 border-gray-100">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Proposé par</p>
                                <p class="text-sm font-bold text-gray-700">{{ nomination.nominator.name }}</p>
                                <p class="text-[10px] text-gray-400 font-medium flex items-center justify-end gap-1 mt-0.5">
                                    <ClockIcon class="h-3 w-3" />
                                    {{ formatDate(nomination.created_at) }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 shrink-0">
                                <button 
                                    @click="processNomination(nomination.id, 'rejected')"
                                    :disabled="form.processing"
                                    class="h-12 px-5 rounded-2xl bg-red-50 text-red-600 font-black text-xs hover:bg-red-600 hover:text-white transition-all active:scale-95 flex items-center gap-2"
                                >
                                    <XMarkIcon class="h-4 w-4" />
                                    Rejeter
                                </button>
                                <button 
                                    @click="processNomination(nomination.id, 'approved')"
                                    :disabled="form.processing"
                                    class="h-12 px-5 rounded-2xl bg-blue-600 text-white font-black text-xs hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 active:scale-95 flex items-center gap-2"
                                >
                                    <CheckCircleIcon class="h-4 w-4" />
                                    Approuver
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-[3rem] p-20 text-center border border-gray-100 shadow-sm">
                    <div class="h-24 w-24 bg-gray-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                        <CheckBadgeIcon class="h-12 w-12 text-gray-300" />
                    </div>
                    <h3 class="text-xl font-black text-gray-900 mb-2">Aucune validation en attente</h3>
                    <p class="text-gray-500 font-medium">Toutes les nominations ont été traitées ou aucune n'a été proposée.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { 
    ShieldCheckIcon, 
    MagnifyingGlassIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const form = useForm({
    reference: '',
})

const submit = () => {
    form.post(route('certificates.search'))
}
</script>

<template>
    <Head title="Vérification d'Acte" />

    <GuestLayout>
        <div class="max-w-4xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-50 text-blue-600 rounded-3xl mb-6 shadow-sm ring-1 ring-blue-100">
                    <ShieldCheckIcon class="h-12 w-12" />
                </div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tighter mb-4">Portail de Vérification Officiel</h1>
                <p class="text-lg text-gray-500 font-medium max-w-2xl mx-auto leading-relaxed">
                    Vérifiez l'authenticité d'un certificat d'état civil en saisissant sa référence unique. 
                    Ce service permet de confirmer la validité des actes délivrés par les centres d'état civil.
                </p>
            </div>

            <div class="max-w-xl mx-auto">
                <div v-if="$page.props.flash.error" class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl flex items-center gap-3 text-red-600 animate-pulse">
                    <ExclamationTriangleIcon class="h-6 w-6" />
                    <p class="text-sm font-bold uppercase tracking-tight">{{ $page.props.flash.error }}</p>
                </div>

                <form @submit.prevent="submit" class="bg-white p-8 rounded-[2.5rem] shadow-2xl shadow-blue-100 border border-gray-100">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2 ml-1">Référence du Certificat</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-600 transition-colors">
                                    <MagnifyingGlassIcon class="h-5 w-5" />
                                </div>
                                <input 
                                    v-model="form.reference"
                                    type="text" 
                                    placeholder="Ex: KOLDA-2026-0001"
                                    class="block w-full pl-14 pr-6 py-5 bg-gray-50 border-0 rounded-2xl focus:ring-2 focus:ring-blue-600 focus:bg-white transition font-bold text-lg uppercase placeholder:normal-case"
                                    required
                                />
                            </div>
                        </div>

                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-5 bg-blue-600 text-white rounded-2xl font-black text-lg shadow-xl shadow-blue-200 hover:bg-black transition-all flex items-center justify-center gap-3 disabled:opacity-50"
                        >
                            <span v-if="form.processing">Recherche...</span>
                            <span v-else>Vérifier l'Authenticité</span>
                            <ShieldCheckIcon v-if="!form.processing" class="h-6 w-6" />
                        </button>
                    </div>
                </form>

                <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6 opacity-60">
                    <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100">
                        <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Sécurité QR Code</h3>
                        <p class="text-sm text-gray-500 font-medium">Les certificats récents comportent un QR code pour une vérification instantanée via mobile.</p>
                    </div>
                    <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100">
                        <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Registre Numérique</h3>
                        <p class="text-sm text-gray-500 font-medium">Les données sont extraites en temps réel du registre central sécurisé de l'État Civil.</p>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

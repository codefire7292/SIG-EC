<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { 
    EnvelopeIcon, 
    ArrowRightIcon,
    ExclamationCircleIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline'

defineProps({
    status: String,
})

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('password.email'))
}
</script>

<template>
    <Head title="Mot de passe oublié" />

    <GuestLayout>
        <div class="min-h-[80vh] flex flex-col items-center justify-center p-4 font-sans">
            <div class="w-full max-w-md bg-white rounded-[2.5rem] shadow-2xl shadow-blue-100 border border-gray-50 overflow-hidden relative">
                <div class="h-2 bg-gradient-to-r from-blue-600 to-indigo-600"></div>
                
                <div class="p-10 md:p-12">
                    <div class="mb-10 text-center">
                        <h1 class="text-3xl font-black text-gray-900 tracking-tighter mb-2 font-sans">Récupération</h1>
                        <p class="text-gray-500 font-medium">Mot de passe oublié ? Pas de souci.</p>
                    </div>

                    <div v-if="status" class="mb-8 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center gap-3 text-emerald-700 text-sm font-bold animate-pulse">
                        <CheckCircleIcon class="h-6 w-6 shrink-0" />
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-8">
                        <p class="text-xs text-gray-400 font-bold leading-relaxed px-1 font-sans">
                            Saisissez votre email. Nous vous enverrons un lien pour choisir un nouveau mot de passe.
                        </p>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2 ml-1">Adresse Email</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-600 transition-colors">
                                    <EnvelopeIcon class="h-5 w-5" />
                                </div>
                                <input 
                                    v-model="form.email"
                                    type="email"
                                    required
                                    placeholder="nom@exemple.sn"
                                    class="block w-full pl-12 pr-4 py-4 bg-gray-50 border-transparent rounded-2xl focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
                                    :class="{'ring-2 ring-red-500 bg-red-50': form.errors.email}"
                                />
                            </div>
                            <span v-if="form.errors.email" class="text-red-500 text-xs mt-2 flex items-center gap-1 font-sans">
                                <ExclamationCircleIcon class="h-4 w-4 font-sans" />
                                {{ form.errors.email }}
                            </span>
                        </div>

                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-4 bg-gray-900 text-white rounded-2xl font-black text-lg shadow-xl shadow-gray-200 hover:bg-black transition flex items-center justify-center gap-2 group disabled:opacity-50"
                        >
                            <span v-if="form.processing">Envoi en cours...</span>
                            <span v-else>Envoyer le lien</span>
                            <ArrowRightIcon v-if="!form.processing" class="h-5 w-5 group-hover:translate-x-1 transition-transform" />
                        </button>
                    </form>

                    <div class="mt-8 text-center">
                        <Link :href="route('login')" class="text-sm font-bold text-blue-600 hover:text-indigo-600 transition font-sans underline">
                            Retour à la connexion
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

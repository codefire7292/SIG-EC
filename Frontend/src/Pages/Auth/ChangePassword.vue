<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { 
    LockClosedIcon, 
    ArrowRightIcon,
    ExclamationCircleIcon,
    ShieldCheckIcon,
    EyeIcon,
    EyeSlashIcon
} from '@heroicons/vue/24/outline'
import { ref } from 'vue'

const showPassword = ref(false)

const form = useForm({
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('password.change.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Changer le mot de passe" />

    <GuestLayout>
        <div class="min-h-[80vh] flex flex-col items-center justify-center p-4 font-sans">
            <div class="w-full max-w-md bg-white rounded-[2.5rem] shadow-2xl shadow-blue-100 border border-gray-50 overflow-hidden relative">
                <div class="h-2 bg-gradient-to-r from-red-600 to-orange-600"></div>
                
                <div class="p-10 md:p-12">
                    <div class="mb-10 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-50 text-red-600 rounded-2xl mb-6">
                            <ShieldCheckIcon class="h-10 w-10" />
                        </div>
                        <h1 class="text-3xl font-black text-gray-900 tracking-tighter mb-2 font-sans">Sécurité Requise</h1>
                        <p class="text-gray-500 font-medium font-sans">Veuillez changer votre mot de passe par défaut pour continuer.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2 ml-1">Nouveau mot de passe</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-600 transition-colors">
                                    <LockClosedIcon class="h-5 w-5" />
                                </div>
                                <input 
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    placeholder="••••••••"
                                    class="block w-full pl-12 pr-12 py-4 bg-gray-50 border-transparent rounded-2xl focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
                                    :class="{'ring-2 ring-red-500 bg-red-50': form.errors.password}"
                                />
                                <button 
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-blue-600 transition"
                                >
                                    <component :is="showPassword ? EyeSlashIcon : EyeIcon" class="h-5 w-5" />
                                </button>
                            </div>
                            <span v-if="form.errors.password" class="text-red-500 text-xs mt-2 flex items-center gap-1 font-sans">
                                <ExclamationCircleIcon class="h-4 w-4" />
                                {{ form.errors.password }}
                            </span>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2 ml-1">Confirmer mot de passe</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-600 transition-colors">
                                    <LockClosedIcon class="h-5 w-5" />
                                </div>
                                <input 
                                    v-model="form.password_confirmation"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    placeholder="••••••••"
                                    class="block w-full pl-12 pr-4 py-4 bg-gray-50 border-transparent rounded-2xl focus:ring-2 focus:ring-blue-600 focus:bg-white transition"
                                    :class="{'ring-2 ring-red-500 bg-red-50': form.errors.password_confirmation}"
                                />
                            </div>
                        </div>

                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-4 bg-gray-900 text-white rounded-2xl font-black text-lg shadow-xl shadow-gray-200 hover:bg-black transition flex items-center justify-center gap-2 group disabled:opacity-50"
                        >
                            <span v-if="form.processing">Mise à jour...</span>
                            <span v-else>Mettre à jour & Continuer</span>
                            <ArrowRightIcon v-if="!form.processing" class="h-5 w-5 group-hover:translate-x-1 transition-transform" />
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

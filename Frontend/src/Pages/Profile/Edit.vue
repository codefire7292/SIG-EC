<script setup>
import { ref, computed } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { 
    UserIcon, 
    KeyIcon, 
    CheckBadgeIcon,
    ExclamationTriangleIcon,
    ArrowUpTrayIcon
} from '@heroicons/vue/24/outline'

const page = usePage()
const user = computed(() => page.props.auth.user)

const form = useForm({
    _method: 'patch',
    name: user.value.name,
    email: user.value.email,
    password: '',
    password_confirmation: '',
    profile_photo: null,
})

const photoPreview = ref(null)
const photoInput = ref(null)

function updateProfile() {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('password', 'password_confirmation')
            photoPreview.value = null
        },
    })
}

function selectNewPhoto() {
    photoInput.value.click()
}

function updatePhotoPreview() {
    const photo = photoInput.value.files[0]
    if (!photo) return

    if (!photo.type.startsWith('image/')) {
        form.errors.profile_photo = "Veuillez sélectionner une image valide."
        return
    }

    form.errors.profile_photo = null
    const reader = new FileReader()
    reader.onload = (e) => {
        const img = new Image()
        img.onload = () => {
            const canvas = document.createElement('canvas')
            const MAX_WIDTH = 500
            const MAX_HEIGHT = 500
            let width = img.width
            let height = img.height

            if (width > height) {
                if (width > MAX_WIDTH) {
                    height *= MAX_WIDTH / width
                    width = MAX_WIDTH
                }
            } else {
                if (height > MAX_HEIGHT) {
                    width *= MAX_HEIGHT / height
                    height = MAX_HEIGHT
                }
            }

            canvas.width = width
            canvas.height = height
            const ctx = canvas.getContext('2d')
            ctx.drawImage(img, 0, 0, width, height)
            
            // Compresser l'image en JPEG à 80% de qualité
            const dataUrl = canvas.toDataURL('image/jpeg', 0.8)
            photoPreview.value = dataUrl

            // Convertir le dataUrl compressé en fichier File pour l'envoi
            fetch(dataUrl)
                .then(res => res.blob())
                .then(blob => {
                    const file = new File([blob], "profile.jpg", { type: "image/jpeg" })
                    form.profile_photo = file
                })
        }
        img.src = e.target.result
    }
    reader.readAsDataURL(photo)
}
</script>

<template>
    <Head title="Mon Profil" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto py-12 px-4">
            <header class="mb-12 text-center">
                <div class="relative inline-block group">
                    <div v-if="photoPreview || user.profile_photo_url" class="h-32 w-32 rounded-[3rem] overflow-hidden mx-auto mb-6 shadow-2xl shadow-green-100 border-4 border-white transition-all scale-105">
                        <img :src="photoPreview || user.profile_photo_url" class="h-full w-full object-cover">
                    </div>
                    <div v-else class="h-32 w-32 text-white rounded-[3rem] flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-green-100 border-4 border-white relative overflow-hidden transition-all group-hover:scale-105" style="background-color: #1E690F;">
                         <UserIcon class="h-16 w-16" />
                    </div>
                    
                    <button @click="selectNewPhoto" type="button" class="absolute bottom-4 -right-2 p-3 bg-white rounded-2xl shadow-lg border border-gray-100 text-[#1E690F] hover:bg-[#1E690F] hover:text-white transition-all active:scale-95">
                        <ArrowUpTrayIcon class="w-5 h-5" />
                    </button>
                    <input ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview">
                </div>

                <h1 class="text-4xl font-black text-gray-900 tracking-tight mb-2">{{ user.name }}</h1>
                <p class="text-gray-500 font-medium tracking-wide italic leading-relaxed">Gérer vos informations personnelles et votre accès sécurisé au SIG-EC.</p>
                <div v-if="form.errors.profile_photo" class="mt-4 text-xs text-red-600 font-bold uppercase tracking-widest bg-red-50 py-2 px-4 rounded-full inline-block border border-red-100">{{ form.errors.profile_photo }}</div>
            </header>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 md:p-12">
                <form @submit.prevent="updateProfile" class="space-y-8">
                    <!-- Basic Info -->
                    <section class="space-y-6">
                        <h2 class="text-lg font-black text-gray-900 flex items-center gap-2">
                             <CheckBadgeIcon class="h-5 w-5 text-green-500" />
                             Informations de Compte
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">Nom Complet</label>
                                <input v-model="form.name" type="text" class="w-full bg-white border border-gray-300 rounded-2xl font-bold py-4 px-6 focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] transition-all">
                                <p v-if="form.errors.name" class="mt-2 text-xs text-red-600 font-bold uppercase tracking-widest">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">Adresse Email</label>
                                <input v-model="form.email" type="email" class="w-full bg-white border border-gray-300 rounded-2xl font-bold py-4 px-6 focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] transition-all">
                                <p v-if="form.errors.email" class="mt-2 text-xs text-red-600 font-bold uppercase tracking-widest">{{ form.errors.email }}</p>
                            </div>
                        </div>
                    </section>

                    <hr class="border-gray-50">

                    <!-- Security -->
                    <section class="space-y-6">
                        <h2 class="text-lg font-black text-gray-900 flex items-center gap-2">
                             <KeyIcon class="h-5 w-5 text-orange-500" />
                             Sécurité (Changer le mot de passe)
                        </h2>
                        <p class="text-sm text-gray-400 font-medium italic">Laissez vide pour conserver votre mot de passe actuel.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">Nouveau Mot de Passe</label>
                                <input v-model="form.password" type="password" class="w-full bg-white border border-gray-300 rounded-2xl font-bold py-4 px-6 focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] transition-all">
                                <p v-if="form.errors.password" class="mt-2 text-xs text-red-600 font-bold uppercase tracking-widest">{{ form.errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">Confirmer le Mot de Passe</label>
                                <input v-model="form.password_confirmation" type="password" class="w-full bg-white border border-gray-300 rounded-2xl font-bold py-4 px-6 focus:outline-none focus:ring-2 focus:ring-[#1E690F] focus:border-[#1E690F] transition-all">
                            </div>
                        </div>
                    </section>

                    <div class="pt-8 border-t border-gray-50 flex items-center justify-between gap-6">
                        <div v-if="form.recentlySuccessful" class="flex items-center gap-2 text-green-600 font-black text-sm animate-bounce">
                            <CheckBadgeIcon class="h-5 w-5" />
                            Modifications enregistrées !
                        </div>
                        <div v-else></div>

                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-10 py-4 text-white rounded-2xl font-black shadow-xl shadow-green-100 hover:bg-[#185709] transition-all disabled:opacity-50 flex items-center gap-3"
                            style="background-color: #1E690F;"
                        >
                            <span v-if="form.processing">Traitement...</span>
                            <span v-else>Enregistrer les modifications</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

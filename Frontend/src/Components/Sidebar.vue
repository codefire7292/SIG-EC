<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { 
    ClipboardDocumentListIcon,
    UserIcon,
    LockClosedIcon,
    HomeIcon,
    UsersIcon,
    ShieldCheckIcon,
    DocumentMagnifyingGlassIcon,
    FingerPrintIcon,
    BuildingLibraryIcon,
    Cog6ToothIcon,
    MapIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    isOpen: Boolean
})

const page = usePage()

const navigation = computed(() => {
    const userPermissions = page.props.auth.user.permissions || []
    const isAdmin = page.props.auth.user.role === 'Administrateur'

    return [
        {
            title: 'Général',
            items: [
                { name: 'Tableau de Bord', href: '/dashboard', icon: HomeIcon, show: true },
            ]
        },
        {
            title: 'État Civil',
            items: [
                { name: 'Naissances', href: '/acts/naissance', icon: FingerPrintIcon, show: true },
                { name: 'Mariages', href: '/acts/mariage', icon: BuildingLibraryIcon, show: true },
                { name: 'Décès', href: '/acts/deces', icon: ShieldCheckIcon, show: true },
            ]
        },
        {
            title: 'Services Certificats',
            items: [
                { name: 'Délivrer un Acte', href: '/civil-certificates/create', icon: ClipboardDocumentListIcon, show: userPermissions.includes('create-drafts') },
                { name: 'Registres Certificats', href: '/civil-certificates', icon: DocumentMagnifyingGlassIcon, show: userPermissions.includes('view-registries') },
            ]
        },
        {
            title: 'Sécurité & Contrôle',
            items: [
                { name: 'Vérification QR', href: '/verify-certificate/search', icon: ShieldCheckIcon, show: true },
                { name: "Journaux d'Audit", href: '#', icon: ClipboardDocumentListIcon, show: isAdmin },
            ]
        },
        {
            title: 'Administration',
            items: [
                { name: 'Utilisateurs', href: '/admin/users', icon: UsersIcon, show: userPermissions.includes('manage-users') },
                { name: 'Centres & Localités', href: '/admin/centers', icon: MapIcon, show: userPermissions.includes('manage-centers') },
                { name: 'Registres', href: '/admin/registries', icon: DocumentMagnifyingGlassIcon, show: userPermissions.includes('manage-centers') },
                { name: 'Paramètres', href: '/admin/settings', icon: Cog6ToothIcon, show: userPermissions.includes('manage-centers') },
            ]
        }
    ]
})

const isUrl = (url) => page.url.startsWith(url)
</script>

<template>
    <aside 
        class="fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 lg:translate-x-0 flex flex-col"
        :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
        style="background: linear-gradient(180deg, #0A2903 0%, #1E690F 70%, #2D5A1E 100%);"
    >
        <!-- Logo & Identité Mairie -->
        <div class="flex-shrink-0 flex flex-col items-center justify-center pt-6 pb-5 px-4 border-b border-white/10">
            <div class="h-16 w-16 rounded-full bg-white flex items-center justify-center shadow-lg border-2 mb-3" style="border-color: #F0C31E;">
                <img 
                    src="/images/logo.png" 
                    alt="Mairie de Enampore" 
                    class="h-12 w-12 object-contain"
                />
            </div>
            <span class="text-white font-black text-sm tracking-widest uppercase leading-none">Mairie de Enampore</span>
            <span class="text-[9px] font-bold uppercase tracking-[0.35em] mt-1" style="color: #F0C31E;">État Civil Numérique</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 mt-4 px-3 space-y-6 overflow-y-auto pb-24 scrollbar-hide">
            <div v-for="section in navigation" :key="section.title">
                <h3 v-if="section.items.some(i => i.show)" class="px-3 text-[9px] font-black uppercase tracking-[0.35em] mb-2" style="color: rgba(240,195,30,0.5);">
                    {{ section.title }}
                </h3>
                <div class="space-y-0.5">
                    <template v-for="item in section.items" :key="item.name">
                        <Link 
                            v-if="item.show"
                            :href="item.disabled ? '#' : item.href"
                            class="flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 group"
                            :class="[
                                isUrl(item.href) && !item.disabled
                                    ? 'text-brand-900' 
                                    : 'text-green-100/80 hover:bg-white/10 hover:text-white',
                                item.disabled ? 'opacity-40 cursor-not-allowed italic font-normal' : ''
                            ]"
                            :style="isUrl(item.href) && !item.disabled ? 'background: #F0C31E;' : ''"
                        >
                            <component 
                                :is="item.icon" 
                                class="mr-3 h-5 w-5 flex-shrink-0 transition-transform duration-200 group-hover:scale-110" 
                                :class="isUrl(item.href) && !item.disabled ? 'text-brand-800' : 'text-green-200/60 group-hover:text-white'"
                            />
                            <span class="truncate">{{ item.name }}</span>
                            <span v-if="item.disabled" class="ml-auto text-[8px] bg-white/10 text-green-200/50 px-1.5 py-0.5 rounded uppercase font-black">Bientôt</span>
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- User Info & Logout -->
        <div class="flex-shrink-0 p-4 border-t border-white/10" style="background: rgba(0,0,0,0.25);">
            <div class="flex items-center gap-3 px-2 py-1 mb-3">
                <div class="h-9 w-9 rounded-full flex items-center justify-center border-2 flex-shrink-0" style="background: rgba(240,195,30,0.15); border-color: rgba(240,195,30,0.4);">
                    <UserIcon class="h-5 w-5 text-green-100" />
                </div>
                <div class="flex flex-col overflow-hidden">
                    <span class="text-xs font-black text-white truncate">{{ page.props.auth.user.name }}</span>
                    <span class="text-[9px] font-bold uppercase tracking-tighter" style="color: rgba(240,195,30,0.7);">{{ page.props.auth.user.role || 'Utilisateur' }}</span>
                </div>
            </div>
            <Link 
                :href="`/logout`" 
                method="post" 
                as="button" 
                class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold text-red-300 hover:bg-red-500/20 hover:text-red-200 rounded-xl transition-all duration-200"
            >
                <LockClosedIcon class="h-4 w-4" />
                Déconnexion
            </Link>
        </div>
    </aside>
</template>

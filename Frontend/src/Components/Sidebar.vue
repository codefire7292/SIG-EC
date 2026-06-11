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
                { name: 'Journaux d\'Audit', href: '#', icon: ClipboardDocumentListIcon, show: isAdmin },
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
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 lg:translate-x-0 flex flex-col"
        :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        <div class="flex-shrink-0 flex items-center justify-center h-20 border-b border-gray-100 px-6">
            <span class="text-2xl font-black text-blue-900 tracking-tighter">SIG-EC</span>
        </div>

        <nav class="flex-1 mt-6 px-4 space-y-8 overflow-y-auto pb-24 scrollbar-hide">
            <div v-for="section in navigation" :key="section.title">
                <h3 v-if="section.items.some(i => i.show)" class="px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">
                    {{ section.title }}
                </h3>
                <div class="space-y-1">
                    <template v-for="item in section.items" :key="item.name">
                        <Link 
                            v-if="item.show"
                            :href="item.disabled ? '#' : item.href"
                            class="flex items-center px-4 py-2 text-sm font-bold rounded-xl transition-all duration-200 group"
                            :class="[
                                isUrl(item.href) && !item.disabled
                                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' 
                                    : 'text-gray-500 hover:bg-blue-50 hover:text-blue-700',
                                item.disabled ? 'opacity-40 cursor-not-allowed italic font-normal' : ''
                            ]"
                        >
                            <component :is="item.icon" class="mr-3 h-5 w-5 transition-transform duration-200 group-hover:scale-110" />
                            <span class="truncate">{{ item.name }}</span>
                            <span v-if="item.disabled" class="ml-auto text-[8px] bg-gray-100 text-gray-400 px-1.5 py-0.5 rounded uppercase font-black">Bientôt</span>
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <div class="flex-shrink-0 p-4 border-t border-gray-200 bg-gray-50/50">
            <div class="flex items-center gap-3 px-3 py-1">
                <div class="h-8 w-8 rounded-full overflow-hidden border border-gray-200 bg-white shadow-sm">
                    <UserIcon class="h-full w-full p-1.5 text-gray-400" />
                </div>
                <div class="flex flex-col overflow-hidden">
                    <span class="text-xs font-black text-gray-900 truncate">{{ page.props.auth.user.name }}</span>
                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">{{ page.props.auth.user.role || 'Utilisateur' }}</span>
                </div>
            </div>
            <Link :href="`/logout`" method="post" as="button" class="mt-4 w-full flex items-center px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50 rounded-md transition-colors">
                <LockClosedIcon class="mr-2 h-4 w-4" />
                Déconnexion
            </Link>
        </div>
    </aside>
</template>

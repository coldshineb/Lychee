<template>
	<Drawer v-model:visible="left_menu_open" :pt:content:class="'flex flex-col justify-start gap-10'">
		<template #header>
			<div class="flex items-center gap-2 text-muted-color hover:text-primary-400">
				<router-link v-if="!isGallery" v-slot="{ href, navigate }" to="/gallery" custom>
					<a v-ripple :href="href" @click="navigate">
						<span class="text-lg font-bold pl-3">Back to Gallery</span>
					</a>
				</router-link>
			</div>
		</template>
		<Menu :model="items" v-if="initData" class="!border-none">
			<template #submenuheader="{ item }">
				<span class="text-primary-emphasis font-bold" :class="item.access !== false ? '' : 'hidden'">
					<!-- @vue-ignore -->
					{{ $t(item.label) }}
				</span>
			</template>
			<template #item="{ item, props }">
				<template v-if="item.access">
					<router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
						<a v-ripple :href="href" v-bind="props.action" @click="navigate">
							<MiniIcon :icon="item.icon ?? ''" :class="'w-3 h-3'" />
							<span class="ml-2">
								<!-- @vue-ignore -->
								{{ $t(item.label) }}
							</span>
							<SETag v-if="item.seTag" />
						</a>
					</router-link>
					<a v-if="item.url" v-ripple :href="item.url" :target="item.target" v-bind="props.action">
						<MiniIcon :icon="item.icon ?? ''" :class="'w-3 h-3'" />
						<span class="ml-2">
							<!-- @vue-ignore -->
							{{ $t(item.label) }}
						</span>
						<SETag v-if="item.seTag" />
					</a>
					<a v-if="!item.route && !item.url" v-ripple v-bind="props.action">
						<MiniIcon :icon="item.icon ?? ''" :class="'w-3 h-3'" />
						<span class="ml-2">
							<!-- @vue-ignore -->
							{{ $t(item.label) }}
						</span>
						<SETag v-if="item.seTag" />
					</a>
				</template>
			</template>
		</Menu>
		<AboutLychee v-model:visible="openLycheeAbout" />
	</Drawer>
</template>
<script setup lang="ts">
import { computed, Ref, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import Drawer from "primevue/drawer";
import Menu from "primevue/menu";
import MiniIcon from "@/components/icons/MiniIcon.vue";
import AboutLychee from "@/components/modals/AboutLychee.vue";
import AuthService from "@/services/auth-service";
import InitService from "@/services/init-service";
import { useAuthStore } from "@/stores/Auth";
import { useLycheeStateStore } from "@/stores/LycheeState";
import AlbumService from "@/services/album-service";
import SETag from "@/components/icons/SETag.vue";
import Constants from "@/services/constants";
import { useRoute } from "vue-router";
import { useTogglablesStateStore } from "@/stores/ModalsState";

type MenyType =
	| {
			label: string;
			icon: string;
			route?: string;
			url?: string;
			target?: string;
			access: boolean;
			seTag?: boolean;
			command?: () => void;
	  }
	| {
			label: string;
			items: MenyType[];
	  };

const initData = ref<App.Http.Resources.Rights.GlobalRightsResource | undefined>(undefined);
const openLycheeAbout = ref(false);
const logsEnabled = ref(true);

const route = useRoute();
const authStore = useAuthStore();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
lycheeStore.init();

const { left_menu_open } = storeToRefs(togglableStore);
const { clockwork_url, is_se_enabled, is_se_preview_enabled, is_se_info_hidden } = storeToRefs(lycheeStore);
const { user } = storeToRefs(authStore);
authStore.getUser();

watch(
	() => user.value,
	(newValue, oldValue) => {
		if (newValue === null) {
			initData.value = undefined;
		} else if (newValue.id !== oldValue?.id) {
			load();
		}
	},
);

load();

function load() {
	InitService.fetchGlobalRights()
		.then((data) => {
			initData.value = data.data;
		})
		.catch((error) => {
			console.error(error);
		});
}

function logout() {
	AuthService.logout().then(() => {
		left_menu_open.value = false;
		initData.value = undefined;
		authStore.setUser(null);
		AlbumService.clearCache();
		window.location.href = Constants.BASE_URL + "/gallery";
	});
}

const canSeeAdmin = computed(() => {
	return (
		initData.value?.settings.can_edit ||
		initData.value?.user_management.can_edit ||
		initData.value?.settings.can_see_diagnostics ||
		initData.value?.settings.can_see_logs ||
		false
	);
});

const isGallery = computed(() => {
	return route.name === "gallery";
});

const items = computed<MenyType[]>(() => {
	if (!initData.value) {
		return [];
	}

	return [
		{
			label: "Admin",
			access: canSeeAdmin.value,
			items: [
				{
					label: "lychee.SETTINGS",
					icon: "cog",
					route: "/settings",
					access: initData.value.settings.can_edit ?? false,
				},
				{
					label: "lychee.USERS",
					icon: "people",
					route: "/users",
					access: initData.value.user_management.can_edit ?? false,
				},
				{
					label: "lychee.DIAGNOSTICS",
					icon: "wrench",
					route: "/diagnostics",
					access: initData.value.settings.can_see_diagnostics ?? false,
				},
				{
					label: "maintenance.title",
					icon: "timer",
					route: "/maintenance",
					access: initData.value.settings.can_edit ?? false,
				},
				{
					label: "lychee.LOGS",
					icon: "excerpt",
					url: Constants.BASE_URL + "/Logs",
					access: (initData.value.settings.can_see_logs ?? false) && logsEnabled.value,
				},
				{
					label: "lychee.LOGS",
					icon: "excerpt",
					access: (initData.value.settings.can_see_logs ?? false) && !logsEnabled.value,
				},
				{
					label: "lychee.JOBS",
					icon: "project",
					route: "/jobs",
					access: initData.value.settings.can_see_logs ?? false,
				},
				{
					label: "Clockwork App",
					icon: "telescope",
					url: clockwork_url.value ?? "",
					access: clockwork_url.value !== null && (initData.value.settings.can_access_dev_tools ?? false),
				},
			],
		},
		{
			label: "lychee.PROFILE",
			items: [
				{
					label: "User",
					icon: "person",
					route: "/profile",
					access: initData.value.user.can_edit ?? false,
				},
				{
					label: "lychee.SHARING",
					icon: "cloud",
					route: "/sharing",
					access: initData.value.root_album.can_upload ?? false,
				},
				{
					label: "Statistics",
					icon: "bar-chart",
					route: "/statistics",
					access: is_se_enabled.value === true,
				},
				{
					label: "Statistics",
					icon: "bar-chart",
					route: "/statistics",
					access: is_se_preview_enabled.value === true,
					seTag: true,
				},
				{
					label: "lychee.SIGN_OUT",
					icon: "account-logout",
					access: true,
					command: logout,
				},
			],
		},
		{
			label: "Lychee",
			items: [
				{
					label: "About",
					icon: "info",
					access: true,
					command: () => (openLycheeAbout.value = true),
				},
				{
					label: "Source",
					icon: "book",
					access: is_se_info_hidden.value === false,
					url: "https://github.com/LycheeOrg/Lychee",
				},
				{
					label: "Support",
					icon: "heart",
					access: is_se_info_hidden.value === false,
					url: "https://lycheeorg.github.io/get-supporter-edition/",
				},
			],
		},
	];
});
</script>

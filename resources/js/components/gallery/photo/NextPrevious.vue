<template>
	<div class="absolute w-1/6 h-1/2 top-1/2 -translate-y-1/2 group" :class="props.is_next ? 'right-0' : 'left-0'">
		<router-link
			:to="{ name: 'photo', params: { albumid: props.albumId, photoid: props.photoId } }"
			:id="props.is_next ? 'nextButton' : 'previousButton'"
			:class="srcClass"
			:style="props.style"
		>
			<MiniIcon :icon="props.is_next ? 'caret-right' : 'caret-left'" class="my-0 h-6 w-5 mr-0 ml-0" />
		</router-link>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import MiniIcon from "@/components/icons/MiniIcon.vue";

const props = defineProps<{
	is_next: boolean;
	albumId: string;
	photoId: string;
	style: string;
}>();

const srcClass = computed(() => {
	let css = "absolute top-1/2 border border-solid border-neutral-200 -mt-5 py-2 px-3 transition-all opacity-0 group-hover:opacity-100 bg-cover";
	if (props.is_next) {
		css += " -right-px group-hover:translate-x-0 translate-x-full";
	} else {
		css += " -left-px group-hover:translate-x-0 -translate-x-full";
	}
	return css;
});
</script>

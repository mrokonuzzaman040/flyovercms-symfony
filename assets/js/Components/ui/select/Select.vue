<script setup>
import { computed } from "vue";
import { SelectRoot } from "reka-ui";

const EMPTY_VALUE = "__empty__";

const props = defineProps({
  open: { type: Boolean, required: false },
  defaultOpen: { type: Boolean, required: false },
  defaultValue: { type: null, required: false },
  modelValue: { type: null, required: false },
  by: { type: [String, Function], required: false },
  dir: { type: String, required: false },
  multiple: { type: Boolean, required: false },
  autocomplete: { type: String, required: false },
  disabled: { type: Boolean, required: false },
  name: { type: String, required: false },
  required: { type: Boolean, required: false },
});
const emits = defineEmits(["update:modelValue", "update:open"]);

const forwarded = computed(() => ({
  ...props,
  modelValue: props.modelValue === "" ? EMPTY_VALUE : props.modelValue,
  defaultValue: props.defaultValue === "" ? EMPTY_VALUE : props.defaultValue,
  "onUpdate:modelValue": (value) => {
    emits("update:modelValue", value === EMPTY_VALUE ? "" : value);
  },
  "onUpdate:open": (value) => {
    emits("update:open", value);
  },
}));
</script>

<template>
  <SelectRoot v-slot="slotProps" data-slot="select" v-bind="forwarded">
    <slot v-bind="slotProps" />
  </SelectRoot>
</template>

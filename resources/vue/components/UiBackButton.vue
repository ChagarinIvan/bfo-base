<template>
    <button class="btn btn-sm me-1"
       :class=this.getClass
       type="button"
    >
        <i class="bi me-1"
           :class=this.icon
           data-bs-toggle="tooltip"
           data-bs-placement="top"
           :title=this.getText
        ></i>
        <span class="d-none d-xl-inline">{{ this.getText }}</span>
    </button>
</template>

<script>

import UiButton from "./UiButton.vue";

export default {
    extends: UiButton,
};
</script>

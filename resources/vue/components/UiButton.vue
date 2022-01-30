<template>
    <a :href=this.url
       class="btn btn-sm me-1"
       :class=this.getClass
       type="button"
       @click="clickEvent"
    >
        <i class="bi me-1"
           :class=this.icon
           data-bs-toggle="tooltip"
           data-bs-placement="top"
           :title=this.getText
        ></i>
        <span class="d-none d-xl-inline">{{ this.getText }}</span>
    </a>
</template>

<script>

import {trans} from 'laravel-vue-i18n';

export default {
    props: {
        color: {
            type: String,
            default: 'primary'
        },
        url: {
            type: String,
            default: ''
        },
        icon: {
            type: String,
            default: 'bi-pencil'
        },
        text: {
            type: String,
            default: 'app.common.edit'
        }
    },
    computed: {
        getClass() {
            return 'btn-outline-' + this.color;
        },
        getText() {
            return trans(this.text);
        },
    },
    methods: {
        clickEvent() {
            this.$emit('clickEvent');
        }
    }
};
</script>

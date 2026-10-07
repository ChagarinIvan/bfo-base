<script setup lang="ts">
import { t } from '../../i18n'
import { updates } from './updates'

const dateFormat = new Intl.DateTimeFormat('be-BY', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    timeZone: 'UTC',
})

function formatDate(date: string): string {
    return dateFormat.format(new Date(`${date}T00:00:00Z`))
}
</script>

<template>
    <section class="updates-page">
        <div class="updates-page__intro">
            <h1 class="page-title">{{ t('spa.updates.title') }}</h1>
            <p>{{ t('spa.updates.intro') }}</p>
            <p class="updates-page__date-note">
                {{ t('spa.updates.date_note') }}
            </p>
        </div>

        <ol class="updates-list">
            <li v-for="entry in updates" :key="entry.spec" class="update-entry">
                <details>
                    <summary>
                        <span class="update-entry__date">{{
                            formatDate(entry.date)
                        }}</span>
                        <span class="update-entry__heading">{{
                            entry.title
                        }}</span>
                        <span class="update-entry__summary">{{
                            entry.summary
                        }}</span>
                    </summary>
                    <div class="update-entry__details">
                        <p>{{ entry.details }}</p>
                        <p v-if="entry.example" class="update-entry__example">
                            <strong>{{ t('spa.updates.example') }}</strong>
                            {{ entry.example }}
                        </p>
                        <RouterLink
                            v-if="entry.link"
                            :to="entry.link.href"
                            class="update-entry__link"
                        >
                            {{ entry.link.label }}
                        </RouterLink>
                    </div>
                </details>
            </li>
        </ol>
    </section>
</template>

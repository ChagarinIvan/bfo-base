import type { InjectionKey, Ref } from 'vue'
import type { Person } from '../../api/types'

export const personContextKey: InjectionKey<Ref<Person | null>> =
    Symbol('person-context')

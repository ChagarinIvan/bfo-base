import type { InjectionKey, Ref } from 'vue'
import type { Cup } from '../../api/types'

export const cupContextKey: InjectionKey<Ref<Cup | null>> = Symbol('cup')

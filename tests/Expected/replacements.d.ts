declare namespace PhpTs {
    namespace Tests {
        namespace Fixtures {
            export interface Scalars {
                string: string
                int: custom_raw_typescript_type
                float: number
                bool: boolean
                object: object
                mixed: any
                null: null
                true: true
                false: false
                noType: unknown
            }
        }
    }
}

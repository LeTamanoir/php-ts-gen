declare namespace Typographos {
    namespace Tests {
        namespace Fixtures {
            export interface Arrays {
                stringList: string[]
                nonEmptyNestedStringList: [[string, ...string[]], ...[string, ...string[]][]]
                stringToIntObject: { [key: string]: number }
                arrayKeyToIntObject: { [key: string]: number }
                arrayKeyToIntObject_2: { [key: string]: number }
                scalars: Typographos.Tests.Fixtures.Scalars[]
                withDocCommentInConstructor: string[]
            }
            export interface Scalars {
                string: string
                int: number
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

declare namespace Typographos {
    namespace Tests {
        namespace Fixtures {
            export interface Child {
                parent: Typographos.Tests.Fixtures._Parent
                parentName: string
            }
            export interface _Parent {
                parentName: string
            }
        }
    }
}

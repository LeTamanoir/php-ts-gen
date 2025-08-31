declare namespace PhpTs {
    namespace Tests {
        namespace Fixtures {
            export interface Child {
                parent: PhpTs.Tests.Fixtures._Parent
                parentName: string
            }
            export interface _Parent {
                parentName: string
            }
        }
    }
}

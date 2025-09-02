declare namespace Typographos {
    export namespace Tests {
        export namespace Fixtures {
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

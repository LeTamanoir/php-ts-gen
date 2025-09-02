declare namespace Typographos {
    export namespace Tests {
        export namespace Fixtures {
            export interface Unions {
                scalar: string | number
                scalarAndSelf: Typographos.Tests.Fixtures.Unions | string
                countableOrIterator: unknown
            }
        }
    }
}

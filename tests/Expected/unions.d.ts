declare namespace Typographos {
    namespace Tests {
        namespace Fixtures {
            export interface Unions {
                scalar: string | number
                scalarAndSelf: Typographos.Tests.Fixtures.Unions | string
                countableOrIterator: unknown
            }
        }
    }
}

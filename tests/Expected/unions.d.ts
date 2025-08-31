declare namespace PhpTs {
    namespace Tests {
        namespace Fixtures {
            export interface Unions {
                scalar: string | number
                scalarAndSelf: PhpTs.Tests.Fixtures.Unions | string
                countableOrIterator: unknown
            }
        }
    }
}

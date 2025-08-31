declare namespace PhpTs {
    namespace Tests {
        namespace Fixtures {
            export interface Nullable {
                maybeString: string | null
                maybeInt: number | null
                maybeSelf: PhpTs.Tests.Fixtures.Nullable | null
                maybeDate: unknown | null
            }
        }
    }
}

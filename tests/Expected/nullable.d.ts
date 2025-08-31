declare namespace Typographos {
    namespace Tests {
        namespace Fixtures {
            export interface Nullable {
                maybeString: string | null
                maybeInt: number | null
                maybeSelf: Typographos.Tests.Fixtures.Nullable | null
                maybeDate: unknown | null
            }
        }
    }
}

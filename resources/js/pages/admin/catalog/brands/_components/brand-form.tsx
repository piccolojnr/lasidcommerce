import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export function BrandForm() {
    return (
        <form className="space-y-6">
            <FormSection title="Brand details" description="Basic branding information for catalog display.">
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                        <Label htmlFor="brand-name">Name</Label>
                        <Input id="brand-name" placeholder="Acme" />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="brand-slug">Slug</Label>
                        <Input id="brand-slug" placeholder="acme" />
                    </div>
                </div>
            </FormSection>
            <FormActions />
        </form>
    );
}

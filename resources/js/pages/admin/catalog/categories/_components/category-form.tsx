import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';

export function CategoryForm() {
    return (
        <form className="space-y-6">
            <FormSection title="Category details" description="Basic information for organizing the catalog.">
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                        <Label htmlFor="category-name">Name</Label>
                        <Input id="category-name" placeholder="New arrivals" />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="category-slug">Slug</Label>
                        <Input id="category-slug" placeholder="new-arrivals" />
                    </div>
                </div>
            </FormSection>
            <FormActions />
        </form>
    );
}

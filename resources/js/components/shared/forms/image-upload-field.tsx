import { ImagePlus, Sparkles, Trash2, Upload } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { FieldError } from '@/components/shared/forms/field-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface ExistingImage {
    id: number;
    url: string;
    is_primary?: boolean;
}

interface ImageUploadFieldProps {
    id: string;
    name: string;
    label: string;
    accept?: string;
    multiple?: boolean;
    existingImages?: ExistingImage[];
    removeFieldName?: string;
    getRemoveValue?: (image: ExistingImage) => string;
    helperText?: string;
    error?: string | null;
}

export function ImageUploadField({
    id,
    name,
    label,
    accept = 'image/jpeg,image/png,image/webp',
    multiple = true,
    existingImages = [],
    removeFieldName = 'remove_image_ids[]',
    getRemoveValue = (image) => String(image.id),
    helperText,
    error,
}: ImageUploadFieldProps) {
    const [selectedFiles, setSelectedFiles] = useState<File[]>([]);
    const [removedIds, setRemovedIds] = useState<number[]>([]);

    const selectedPreviews = useMemo(
        () =>
            selectedFiles.map((file) => ({
                name: file.name,
                url: URL.createObjectURL(file),
            })),
        [selectedFiles],
    );

    useEffect(() => {
        return () => {
            selectedPreviews.forEach((image) => URL.revokeObjectURL(image.url));
        };
    }, [selectedPreviews]);

    const visibleExistingImages = existingImages.filter(
        (image) => !removedIds.includes(image.id),
    );

    return (
        <div className="space-y-5">
            <div className="space-y-2">
                <Label htmlFor={id}>{label}</Label>
                <div className="space-y-3" />
                <label
                    htmlFor={id}
                    className="group block cursor-pointer rounded-2xl border border-dashed border-border/80 bg-linear-to-br from-muted/60 via-background to-muted/20 p-5 transition hover:border-primary/50 hover:bg-muted/40"
                >
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div className="flex items-start gap-3">
                            <div className="rounded-2xl bg-primary/10 p-3 text-primary">
                                <ImagePlus className="size-5" />
                            </div>
                            <div className="space-y-1">
                                <p className="font-medium">
                                    Drop product imagery here or click to browse
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Use clean packshots, lifestyle images, or
                                    detail shots to make the product page feel
                                    real.
                                </p>
                                {helperText ? (
                                    <p className="text-xs text-muted-foreground">
                                        {helperText}
                                    </p>
                                ) : null}
                            </div>
                        </div>
                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                            <Upload className="size-4" />
                            <span>
                                {multiple
                                    ? 'Multiple images allowed'
                                    : 'Single image'}
                            </span>
                        </div>
                    </div>
                </label>
                <Input
                    id={id}
                    name={name}
                    type="file"
                    multiple={multiple}
                    accept={accept}
                    className="hidden"
                    onChange={(event) => {
                        const nextFiles = Array.from(event.target.files ?? []);

                        setSelectedFiles((currentFiles) => {
                            if (!multiple) {
                                return nextFiles;
                            }

                            const existingKeys = new Set(
                                currentFiles.map(
                                    (file) =>
                                        `${file.name}:${file.size}:${file.lastModified}`,
                                ),
                            );

                            const uniqueNextFiles = nextFiles.filter((file) => {
                                const key = `${file.name}:${file.size}:${file.lastModified}`;

                                return !existingKeys.has(key);
                            });

                            return [...currentFiles, ...uniqueNextFiles];
                        });

                        // Allow selecting the same file again in a later interaction.
                        event.target.value = '';
                    }}
                />
                <FieldError message={error} />
            </div>

            {visibleExistingImages.length > 0 ? (
                <div className="space-y-3">
                    <div className="flex items-center gap-2 text-sm font-medium">
                        <Sparkles className="size-4 text-primary" />
                        <span>Current gallery</span>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {visibleExistingImages.map((image) => (
                            <div
                                key={image.id}
                                className="overflow-hidden rounded-2xl border border-border/70 bg-background"
                            >
                                <img
                                    src={image.url}
                                    alt=""
                                    className="aspect-4/3 w-full object-cover"
                                />
                                <div className="flex items-center justify-between gap-3 p-3">
                                    <div className="space-y-1">
                                        <p className="text-sm font-medium">
                                            Existing image
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {image.is_primary
                                                ? 'Primary display image'
                                                : 'Gallery image'}
                                        </p>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        className="text-destructive hover:text-destructive"
                                        onClick={() =>
                                            setRemovedIds((current) => [
                                                ...current,
                                                image.id,
                                            ])
                                        }
                                    >
                                        <Trash2 className="size-4" />
                                    </Button>
                                </div>
                                {removedIds.includes(image.id) ? (
                                    <input
                                        type="hidden"
                                        name={removeFieldName}
                                        value={image.id}
                                    />
                                ) : null}
                            </div>
                        ))}
                    </div>
                </div>
            ) : null}

            {removedIds.map((idValue) => {
                const image = existingImages.find(
                    (item) => item.id === idValue,
                );

                return (
                    <input
                        key={idValue}
                        type="hidden"
                        name={removeFieldName}
                        value={image ? getRemoveValue(image) : String(idValue)}
                    />
                );
            })}

            {selectedPreviews.length > 0 ? (
                <div className="space-y-3">
                    <div className="flex items-center justify-between gap-3">
                        <div>
                            <p className="text-sm font-medium">
                                Queued uploads
                            </p>
                            <p className="text-xs text-muted-foreground">
                                {selectedPreviews.length} image
                                {selectedPreviews.length === 1 ? '' : 's'} ready
                                to upload
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() => setSelectedFiles([])}
                        >
                            Clear selection
                        </Button>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {selectedPreviews.map((image) => (
                            <div
                                key={image.name}
                                className="overflow-hidden rounded-2xl border border-border/70 bg-background"
                            >
                                <img
                                    src={image.url}
                                    alt=""
                                    className="aspect-4/3 w-full object-cover"
                                />
                                <div className="p-3">
                                    <p className="truncate text-sm font-medium">
                                        {image.name}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        Will be uploaded on save
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            ) : null}
        </div>
    );
}

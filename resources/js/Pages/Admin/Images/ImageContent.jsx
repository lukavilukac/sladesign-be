import { useState, useRef, useEffect } from 'react';
import { router } from '@inertiajs/react';
import ReactCrop, { makeAspectCrop, centerCrop, convertToPixelCrop } from 'react-image-crop';
import { canvasPreview } from '@/Components/Core/canvasPreview.jsx';
import 'react-image-crop/dist/ReactCrop.css';

export default function ImageContent({ close, ratioWidth, ratioHeight, entity }) {
    const [imgSrc, setImgSrc] = useState('');
    const [alt, setAlt] = useState('');
    const [crop, setCrop] = useState();
    const [error, setError] = useState('');
    const [processing, setProcessing] = useState(false);
    const imgRef = useRef(null);
    const inputRef = useRef(null);
    const previewRef = useRef(null);
    const busyRef = useRef(false);
    const aspect = ratioWidth / ratioHeight;

    useEffect(() => () => {
        if (imgSrc) URL.revokeObjectURL(imgSrc);
    }, [imgSrc]);

    useEffect(() => {
        const image = imgRef.current;
        const canvas = previewRef.current;
        if (!image || !canvas || !crop?.width || !crop?.height) return;
        // Percent coordinates remain valid when the modal changes size.
        canvasPreview(image, canvas, convertToPixelCrop(crop, image.width, image.height));
    }, [crop, imgSrc]);

    function onSelectFile(event) {
        const file = event.target.files?.[0];
        event.target.value = '';
        if (!file) return;
        setError('');
        setCrop(undefined);
        setImgSrc(URL.createObjectURL(file));
    }

    function onImageLoad(event) {
        const { width, height } = event.currentTarget;
        setCrop(centerCrop(makeAspectCrop({ unit: '%', width: 90 }, aspect, width, height), width, height));
    }

    async function storeImage() {
        const image = imgRef.current;
        if (busyRef.current || !image || !crop?.width || !crop?.height) return;
        busyRef.current = true;
        setProcessing(true);
        setError('');
        try {
            // Render the current selection immediately, independently of the preview.
            const canvas = document.createElement('canvas');
            await canvasPreview(image, canvas, convertToPixelCrop(crop, image.width, image.height));
            const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.9));
            if (!blob) throw new Error('Could not prepare the cropped image. Please choose another image.');
            const data = new FormData();
            data.append('image', blob, 'image.jpg');
            data.append('alt', alt);
            router.post(route('admin.images.upload', entity), data, {
                onSuccess: () => close(),
                onError: errors => setError(errors.image || errors.alt || 'Upload failed. Please try again.'),
                onFinish: () => {
                    busyRef.current = false;
                    setProcessing(false);
                },
            });
        } catch (error) {
            setError(error.message || 'Could not prepare the cropped image.');
            busyRef.current = false;
            setProcessing(false);
        }
    }

    return (
        <div className="space-y-4">
            <div>
                <label htmlFor="image-alt" className="block text-sm font-medium text-gray-900">Image ALT tag</label>
                <input id="image-alt" type="text" value={alt} onChange={event => setAlt(event.target.value)}
                    disabled={processing} className="mt-2 w-full rounded-md border-gray-300" />
            </div>
            <input ref={inputRef} type="file" accept="image/*" onChange={onSelectFile} className="hidden" />
            <button type="button" onClick={() => inputRef.current.click()} disabled={processing}
                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">
                Choose Image
            </button>
            {imgSrc && (
                <div className="flex justify-center rounded-md bg-gray-100 p-2">
                    <ReactCrop crop={crop} onChange={(_, percentCrop) => setCrop(percentCrop)}
                        aspect={aspect} disabled={processing} keepSelection>
                        <img key={imgSrc} ref={imgRef} src={imgSrc} alt="Image to crop" onLoad={onImageLoad}
                            onError={() => { setCrop(undefined); setError('This image could not be opened. Try a JPG, PNG or WebP file.'); }}
                            style={{ maxHeight: '50vh', maxWidth: '100%', width: 'auto', height: 'auto' }} />
                    </ReactCrop>
                </div>
            )}
            {crop?.width > 0 && crop?.height > 0 && (
                <div>
                    <p className="mb-2 text-sm text-gray-600">Preview</p>
                    <canvas ref={previewRef} className="block max-w-full rounded border border-gray-300"
                        style={{ width: 120, height: 'auto' }} />
                </div>
            )}
            {error && <p role="alert" className="text-sm text-red-600">{error}</p>}
            <div className="flex justify-end gap-3">
                <button type="button" onClick={close} disabled={processing}
                    className="rounded-md border border-gray-300 px-4 py-2 text-sm disabled:opacity-50">Cancel</button>
                <button type="button" onClick={storeImage} disabled={processing || !crop?.width || !crop?.height}
                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">
                    {processing ? 'Uploading…' : 'Confirm'}
                </button>
            </div>
        </div>
    );
}

import AdminLayout from "@/Layouts/AdminLayout.jsx";
import React, {useState, useRef, useEffect} from 'react';
import {router, usePage} from "@inertiajs/react";

import ReactCrop, {
    centerCrop,
    makeAspectCrop,
} from 'react-image-crop'
import {canvasPreview} from "@/Components/Core/canvasPreview.jsx";
import {useDebounceEffect} from "@/Components/Core/useDebounceEffect.jsx";

import 'react-image-crop/dist/ReactCrop.css'
import ModalButtons from "@/Components/Modals/ModalButtons.jsx";

// This is to demonstate how to make and center a % aspect crop
// which is a bit trickier so we use some helper functions.
function centerAspectCrop(
    mediaWidth,
    mediaHeight,
    aspect,
) {
    return centerCrop(
        makeAspectCrop(
            {
                unit: '%',
                width: 90,
            },
            aspect,
            mediaWidth,
            mediaHeight,
        ),
        mediaWidth,
        mediaHeight,
    )
}

export default function ImageContent({close, ratioWidth, ratioHeight, entity}) {
    const [imgSrc, setImgSrc] = useState('')
    const [alt, setAlt] = useState('');
    const previewCanvasRef = useRef(null)
    const imgRef = useRef(null)
    const hiddenAnchorRef = useRef(null)
    const inputChoseFile = useRef(null)
    const blobUrlRef = useRef('')
    const [crop, setCrop] = useState()
    const [completedCrop, setCompletedCrop] = useState()
    const [scale, setScale] = useState(1)
    const [rotate, setRotate] = useState(0)
    const [aspect, setAspect] = useState(() => ratioWidth / ratioHeight)

    function onSelectFile(e) {
        if (e.target.files && e.target.files.length > 0) {
            setCrop(undefined) // Makes crop preview update between images.
            const reader = new FileReader()
            reader.addEventListener('load', () =>
                setImgSrc(reader.result?.toString() || ''),
            )
            reader.readAsDataURL(e.target.files[0])
        }
    }

    function onImageLoad(e) {
        if (aspect) {
            const { width, height } = e.currentTarget
            setCrop(centerAspectCrop(width, height, aspect))
        }
    }

    function storeImage() {
        if (!previewCanvasRef.current) {
            throw new Error('Crop canvas does not exist');
        }

        previewCanvasRef.current.toBlob(async (blob) => {
            if (!blob) {
                throw new Error('Failed to create blob');
            }

            // Convert the blob to JPEG format
            const jpegBlob = await new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    canvas.toBlob(resolve, 'image/jpeg', 0.8); // Adjust quality as needed
                };
                img.src = URL.createObjectURL(blob);
            });

            // Create FormData and append the JPEG blob
            const formData = new FormData();
            formData.append('image', jpegBlob, 'image.jpg'); // Use '.jpg' extension for JPEG
            formData.append('alt', alt);

            // Send the FormData with the JPEG blob to the server
            await router.post(route('admin.images.upload', entity), formData);

            // Close the modal or perform other actions
            close();
        });
    }



    useDebounceEffect(
        async () => {
            if (
                completedCrop?.width &&
                completedCrop?.height &&
                imgRef.current &&
                previewCanvasRef.current
            ) {
                // We use canvasPreview as it's much faster than imgPreview.
                canvasPreview(
                    imgRef.current,
                    previewCanvasRef.current,
                    completedCrop,
                    scale,
                    rotate,
                )
            }
        },
        100,
        [completedCrop, scale, rotate],
    )

    function handleToggleAspectClick() {
        if (aspect) {
            setAspect(undefined)
        } else if (imgRef.current) {
            const { width, height } = imgRef.current
            setAspect(16 / 9)
            setCrop(centerAspectCrop(width, height, 16 / 9))
        }
    }

    const handleAltChange = (e) => {
        setAlt(e.target.value);
    }

    useEffect(() => {
        if (inputChoseFile && inputChoseFile.current) {
            inputChoseFile.current.click();
        }
    },[inputChoseFile]);

    return (
        <>

            <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 mb-4">

                <div className="sm:col-span-4">
                    <label htmlFor="name" className="block text-sm font-medium leading-6 text-gray-900">
                        Image ALT tag
                    </label>
                    <div className="mt-2">
                        <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                            <input
                                type="text"
                                name="name"
                                id="name"
                                className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                placeholder="Some fancy name"
                                value={alt} onChange={handleAltChange}
                            />
                        </div>
                    </div>
                </div>

            </div>

            <div className="hidden">
                <input type="file" accept="image/*" onChange={onSelectFile} ref={inputChoseFile} />
                {/*<div>
                    <label htmlFor="scale-input">Scale: </label>
                    <input
                        id="scale-input"
                        type="number"
                        step="0.1"
                        value={scale}
                        disabled={!imgSrc}
                        onChange={(e) => setScale(Number(e.target.value))}
                    />
                </div>
                <div>
                    <label htmlFor="rotate-input">Rotate: </label>
                    <input
                        id="rotate-input"
                        type="number"
                        value={rotate}
                        disabled={!imgSrc}
                        onChange={(e) =>
                            setRotate(Math.min(180, Math.max(-180, Number(e.target.value))))
                        }
                    />
                </div>
                <div>
                    <button onClick={handleToggleAspectClick}>
                        Toggle aspect {aspect ? 'off' : 'on'}
                    </button>
                </div>*/}
            </div>
            {!!imgSrc && (
                <ReactCrop
                    crop={crop}
                    onChange={(_, percentCrop) => setCrop(percentCrop)}
                    onComplete={(c) => setCompletedCrop(c)}
                    aspect={aspect}
                >
                    <img
                        ref={imgRef}
                        alt="Crop me"
                        src={imgSrc}
                        style={{ transform: `scale(${scale}) rotate(${rotate}deg)` }}
                        onLoad={onImageLoad}
                    />
                </ReactCrop>
            )}
            {!!completedCrop && (
                <>
                    <div>
                        <canvas
                            ref={previewCanvasRef}
                            style={{
                                border: '1px solid black',
                                objectFit: 'contain',
                                width: completedCrop.width,
                                height: completedCrop.height,
                            }}
                        />
                    </div>
                </>
            )}
            <ModalButtons onClickCancel={close} onClickConfirm={storeImage}/>

        </>
    )
}

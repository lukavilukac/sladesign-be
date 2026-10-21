import AdminLayout from "@/Layouts/AdminLayout.jsx";
import ImageUploadModal from "@/Components/Modals/ImageUploadModal.jsx";
import { useState } from "react";
import ImageContent from "@/Pages/Admin/Images/ImageContent.jsx";
import RemoveImageModal from "@/Components/Modals/RemoveImageModal.jsx";
import ImageListItem from "@/Components/Core/ImageListItem.jsx";
import { usePage,router } from "@inertiajs/react";

const Images = ({ images, ratioWidth, ratioHeight, entity }) => {
    const [openUploadImageModal, setOpenUploadImageModal] = useState(false);
    const [openRemoveImageModal, setOpenRemoveImageModal] = useState(false);
    const [imageToRemove, setImageToRemove] = useState(null);
    const [imageType, setImageType] = useState(null);
    const { url, component } = usePage();
    const [alt, setAlt] = useState('');

    const removeImage = (image) => {
        setImageToRemove(image);
        setOpenRemoveImageModal(true);
        setImageType(url.split('/')[2]);
    }

    /**
     * Simple image upload start
     */
    const [file, setFile] = useState(null);

    const handleFileChange = (e) => {
        setFile(e.target.files[0]);
    };

    const handleAltChange = (e) => {
        setAlt(e.target.value);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('image', file);
        formData.append('alt', alt);
        setFile(null)

        router.post(route('admin.images.upload', entity), formData);
    };
    /**
     * Simple image upload end
     */

    return (
        <AdminLayout>

            <div className='container mx-auto p-4'>
                <h1 className='text-2xl font-bold mb-4'>Simple Upload Image</h1>
                <form onSubmit={handleSubmit} className='space-y-4'>
                    <input
                        type="file"
                        onChange={handleFileChange}
                        className='block w-full text-sm text-gray-500
                           file:mr-4 file:py-2 file:px-4
                           file:rounded-full file:border-0
                           file:text-sm file:font-semibold
                           file:bg-violet-50 file:text-violet-700
                           hover:file:bg-violet-100
                           focus:outline-hidden focus:ring-2 focus:ring-violet-500 focus:ring-offset-2'
                    />
                    {file && (
                        <>
                            <input
                                type="text"
                                placeholder="Enter alt text"
                                value={alt}
                                onChange={handleAltChange}
                                className='block w-full text-sm text-gray-500
                                       py-2 px-4 rounded-md border border-gray-300
                                       focus:outline-hidden focus:ring-2 focus:ring-violet-500 focus:ring-offset-2'
                            />
                            <button
                                type="submit"
                                className='bg-blue-500 text-white font-semibold py-2 px-4 rounded-full
                                   hover:bg-blue-600 focus:outline-hidden focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'>
                                Upload
                            </button>
                        </>
                    )}
                </form>
            </div>

            <button
                type="button"
                onClick={() => setOpenUploadImageModal(true)}
                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                Upload Image
            </button>

            <ImageUploadModal
                open={openUploadImageModal}
                setOpen={setOpenUploadImageModal}
            >
                <ImageContent close={() => setOpenUploadImageModal(false)} ratioWidth={ratioWidth}
                              ratioHeight={ratioHeight} entity={entity}/>
            </ImageUploadModal>

            <RemoveImageModal
                open={openRemoveImageModal}
                setOpen={setOpenRemoveImageModal}
                image={imageToRemove}
                imageType={imageType}
            />

            <ul role="list"
                className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mt-8">
                {images.map((file) => (
                    <ImageListItem key={file.id} file={file} removeImage={removeImage}/>
                ))}
            </ul>
        </AdminLayout>
    );
};

export default Images;

import AdminLayout from "@/Layouts/AdminLayout.jsx";
import ImageUploadModal from "@/Components/Modals/ImageUploadModal.jsx";
import { useState } from "react";
import ImageContent from "@/Pages/Admin/Images/ImageContent.jsx";
import RemoveImageModal from "@/Components/Modals/RemoveImageModal.jsx";
import ImageListItem from "@/Components/Core/ImageListItem.jsx";
import { usePage } from "@inertiajs/react";

const Images = ({images, ratioWidth, ratioHeight, entity}) => {

    const [openUploadImageModal, setOpenUploadImageModal] = useState(false);
    const [openRemoveImageModal, setOpenRemoveImageModal] = useState(false);
    const [imageToRemove, setImageToRemove] = useState(null);
    const [imageType, setImageType] = useState(null);
    const { url, component } = usePage();

    const removeImage = (image) => {
        setImageToRemove(image);
        setOpenRemoveImageModal(true);
        setImageType(url.split('/')[2]);
    }

    return <AdminLayout>

        <button
            type="button"
            onClick={() => setOpenUploadImageModal(true)}
            className="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
        >
            Upload Image
        </button>

        <ImageUploadModal
            open={openUploadImageModal}
            setOpen={setOpenUploadImageModal}
        >
            <ImageContent close={() => setOpenUploadImageModal(false)} ratioWidth={ratioWidth} ratioHeight={ratioHeight} entity={entity}/>
        </ImageUploadModal>

        <RemoveImageModal
            open={openRemoveImageModal}
            setOpen={setOpenRemoveImageModal}
            image={imageToRemove}
            imageType={imageType}
        >

        </RemoveImageModal>

        <ul role="list" className="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 sm:gap-x-6 lg:grid-cols-4 xl:gap-x-8 mt-8">
            {images.map((file) => (
                <ImageListItem key={file.id} file={file} removeImage={removeImage}/>
            ))}
        </ul>

    </AdminLayout>
}

export default Images;

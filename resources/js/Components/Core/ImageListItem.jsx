const ImageListItem = ({file, removeImage}) => {
    return <li key={file.path} className="relative">
        <div className="group aspect-h-7 aspect-w-10 block w-full overflow-hidden rounded-lg bg-gray-100">
            <img src={file.path} alt="" className="pointer-events-none object-cover" />

        </div>
        <p className="pointer-events-none mt-2 block truncate text-sm font-medium text-gray-900">{file.alt}</p>
        <p className="pointer-events-none block text-sm font-medium text-gray-500">{file.size}</p>
        {removeImage &&
            <button
                type="button"
                className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                onClick={() => removeImage(file)}
            >
                Remove Image
            </button>
        }
    </li>
}

export default ImageListItem;
